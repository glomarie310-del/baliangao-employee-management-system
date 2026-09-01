<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $role = $request->input('role');

        $users = User::query()
            ->with('employee')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas(
                            'employee',
                            function ($employeeQuery) use ($search) {
                                $employeeQuery
                                    ->where(
                                        'employee_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'first_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'last_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'department',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $employees = Employee::query()
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,

            'employees' => $employees,

            'filters' => [
                'search' => $search,
                'role' => $role,
            ],

            'summary' => [
                'total' => User::count(),

                'active' => User::where(
                    'is_active',
                    true
                )->count(),

                'inactive' => User::where(
                    'is_active',
                    false
                )->count(),

                'administrators' => User::where(
                    'role',
                    User::ROLE_ADMIN
                )->count(),

                'linked' => Employee::whereNotNull(
                    'user_id'
                )->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        $employeeId = $validated['employee_id'] ?? null;

        unset($validated['employee_id']);

        if ($employeeId) {
            $this->ensureEmployeeAvailable($employeeId);
        }

        $validated['password'] = Hash::make(
            $validated['password']
        );

        DB::transaction(function () use (
            $validated,
            $employeeId
        ) {
            $user = User::create($validated);

            if ($employeeId) {
                Employee::whereKey($employeeId)
                    ->update([
                        'user_id' => $user->id,
                    ]);
            }
        });

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User account created successfully.'
            );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated = $this->validateUser(
            $request,
            $user
        );

        if (
            $request->user()->is($user) &&
            ! $validated['is_active']
        ) {
            return back()->with(
                'error',
                'You cannot deactivate your own account.'
            );
        }

        $employeeId = $validated['employee_id'] ?? null;

        unset($validated['employee_id']);

        /*
         * Keep the existing password when the password
         * field is empty.
         */
        if (
            ! array_key_exists('password', $validated) ||
            blank($validated['password'])
        ) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make(
                $validated['password']
            );
        }

        if ($employeeId) {
            $this->ensureEmployeeAvailable(
                $employeeId,
                $user
            );
        }

        DB::transaction(function () use (
            $user,
            $validated,
            $employeeId
        ) {
            $user->update($validated);

            /*
             * Remove the account's previous employee link.
             */
            Employee::where(
                'user_id',
                $user->id
            )->update([
                'user_id' => null,
            ]);

            /*
             * Link the selected employee record.
             */
            if ($employeeId) {
                Employee::whereKey($employeeId)
                    ->update([
                        'user_id' => $user->id,
                    ]);
            }
        });

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User account and employee record updated successfully.'
            );
    }

    public function toggle(
        Request $request,
        User $user
    ): RedirectResponse {
        if ($request->user()->is($user)) {
            return back()->with(
                'error',
                'You cannot deactivate your own account.'
            );
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return back()->with(
            'success',
            $user->is_active
                ? 'User account activated successfully.'
                : 'User account deactivated successfully.'
        );
    }

    private function validateUser(
        Request $request,
        ?User $user = null
    ): array {
        $requiresEmployeeLink = in_array(
            $request->input('role'),
            [
                User::ROLE_DEPARTMENT_HEAD,
                User::ROLE_EMPLOYEE,
            ],
            true
        );

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($user?->id),
            ],

            'password' => [
                $user ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_HRMO_STAFF,
                    User::ROLE_DEPARTMENT_HEAD,
                    User::ROLE_EMPLOYEE,
                ]),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'employee_id' => [
                $requiresEmployeeLink
                    ? 'required'
                    : 'nullable',
                'exists:employees,id',
            ],
        ], [
            'employee_id.required' =>
                'Department Head and Regular Employee accounts must be linked to an employee record.',

            'employee_id.exists' =>
                'The selected employee record does not exist.',

            'password.required' =>
                'A password is required when creating an account.',

            'password.confirmed' =>
                'The password confirmation does not match.',

            'password.min' =>
                'The password must contain at least 8 characters.',
        ]);
    }

    private function ensureEmployeeAvailable(
        int|string $employeeId,
        ?User $currentUser = null
    ): void {
        $employee = Employee::query()
            ->findOrFail($employeeId);

        if (
            $employee->user_id !== null &&
            (int) $employee->user_id !==
                (int) $currentUser?->id
        ) {
            throw ValidationException::withMessages([
                'employee_id' =>
                    'This employee record is already linked to another user account.',
            ]);
        }
    }
}