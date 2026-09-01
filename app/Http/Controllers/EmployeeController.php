<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim(
            (string) $request->input('search')
        );

        $department = $request->input('department');
        $status = $request->input('status');
        $user = $request->user();

        $baseQuery = Employee::query();

        /*
         * Department heads can only access employees
         * under their own department.
         */
        if ($user->role === 'department_head') {
            $departmentHeadRecord = Employee::query()
                ->where('user_id', $user->id)
                ->first();

            if (! $departmentHeadRecord) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->where(
                    'department',
                    $departmentHeadRecord->department
                );
            }
        }

        $employees = (clone $baseQuery)
            ->when($search, function (
                Builder $query
            ) use ($search) {
                $query->where(function (
                    Builder $employeeQuery
                ) use ($search) {
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
                            'middle_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'last_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'position',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->when(
                $department &&
                $user->role !== 'department_head',
                function (Builder $query) use ($department) {
                    $query->where(
                        'department',
                        $department
                    );
                }
            )
            ->when(
                $status,
                function (Builder $query) use ($status) {
                    $query->where(
                        'status',
                        $status
                    );
                }
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'Employees/Index',
            [
                'employees' => $employees,

                'filters' => [
                    'search' => $search,
                    'department' => $department,
                    'status' => $status,
                ],

                'departments' => (clone $baseQuery)
                    ->whereNotNull('department')
                    ->distinct()
                    ->orderBy('department')
                    ->pluck('department'),

                'summary' => [
                    'total' => (clone $baseQuery)->count(),

                    'active' => (clone $baseQuery)
                        ->where('status', 'Active')
                        ->count(),

                    'inactive' => (clone $baseQuery)
                        ->where('status', 'Inactive')
                        ->count(),

                    'retired' => (clone $baseQuery)
                        ->where('status', 'Retired')
                        ->count(),
                ],

                'canManage' => in_array(
                    $user->role,
                    [
                        'admin',
                        'hrmo_staff',
                    ],
                    true
                ),
            ]
        );
    }

    public function show(
        Request $request,
        Employee $employee
    ): Response {
        $this->authorizeEmployeeView(
            $request,
            $employee
        );

        return Inertia::render(
            'Employees/Show',
            [
                'employee' => $employee,
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $this->ensureCanManage($request);

        Employee::create(
            $this->validateEmployee($request)
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Employee record created successfully.'
            );
    }

    public function update(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $this->ensureCanManage($request);

        $employee->update(
            $this->validateEmployee(
                $request,
                $employee
            )
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Employee record updated successfully.'
            );
    }

    public function destroy(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $this->ensureCanManage($request);

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Employee record deleted successfully.'
            );
    }

    private function authorizeEmployeeView(
        Request $request,
        Employee $employee
    ): void {
        $user = $request->user();

        if (
            in_array(
                $user->role,
                ['admin', 'hrmo_staff'],
                true
            )
        ) {
            return;
        }

        if ($user->role === 'department_head') {
            $departmentHeadRecord = Employee::query()
                ->where('user_id', $user->id)
                ->first();

            if (
                ! $departmentHeadRecord ||
                $employee->department !==
                    $departmentHeadRecord->department
            ) {
                abort(
                    403,
                    'You can only view employees under your department.'
                );
            }

            return;
        }

        abort(403);
    }

    private function ensureCanManage(
        Request $request
    ): void {
        if (
            ! in_array(
                $request->user()->role,
                ['admin', 'hrmo_staff'],
                true
            )
        ) {
            abort(403);
        }
    }

    private function validateEmployee(
        Request $request,
        ?Employee $employee = null
    ): array {
        return $request->validate([
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'employees',
                    'employee_number'
                )->ignore($employee?->id),
            ],
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],
            'sex' => [
                'required',
                Rule::in([
                    'Male',
                    'Female',
                ]),
            ],
            'birth_date' => [
                'nullable',
                'date',
                'before:today',
            ],
            'civil_status' => [
                'nullable',
                Rule::in([
                    'Single',
                    'Married',
                    'Widowed',
                    'Separated',
                    'Annulled',
                ]),
            ],
            'contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'department' => [
                'required',
                'string',
                'max:255',
            ],
            'position' => [
                'required',
                'string',
                'max:255',
            ],
            'salary_grade' => [
                'nullable',
                'integer',
                'between:1,33',
            ],
            'step' => [
                'nullable',
                'integer',
                'between:1,8',
            ],
            'employment_status' => [
                'required',
                Rule::in(['Regular']),
            ],
            'date_hired' => [
                'nullable',
                'date',
            ],
            'gsis_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'pagibig_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'philhealth_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'tin_number' => [
                'nullable',
                'string',
                'max:50',
            ],
            'status' => [
                'required',
                Rule::in([
                    'Active',
                    'Inactive',
                    'Retired',
                    'Separated',
                ]),
            ],
        ]);
    }
}