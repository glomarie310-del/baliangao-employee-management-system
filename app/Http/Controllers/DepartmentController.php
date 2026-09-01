<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));

        $departments = Department::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($departmentQuery) use ($search) {
                    $departmentQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('office_head', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) {
                return [
                    ...$department->toArray(),

                    'employee_count' => Employee::where(
                        'department',
                        $department->name
                    )->count(),
                ];
            });

        return Inertia::render('Departments/Index', [
            'departments' => $departments,

            'filters' => [
                'search' => $search,
            ],

            'summary' => [
                'total' => Department::count(),
                'active' => Department::where('status', 'Active')->count(),
                'inactive' => Department::where('status', 'Inactive')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($this->validateDepartment($request));

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function update(
        Request $request,
        Department $department
    ): RedirectResponse {
        $oldName = $department->name;

        $data = $this->validateDepartment($request, $department);

        $department->update($data);

        if ($oldName !== $department->name) {
            Employee::where('department', $oldName)
                ->update([
                    'department' => $department->name,
                ]);
        }

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $employeeCount = Employee::where(
            'department',
            $department->name
        )->count();

        if ($employeeCount > 0) {
            return redirect()
                ->route('departments.index')
                ->with(
                    'error',
                    'This department cannot be deleted because employees are assigned to it.'
                );
        }

        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    private function validateDepartment(
        Request $request,
        ?Department $department = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('departments', 'code')
                    ->ignore($department?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->ignore($department?->id),
            ],

            'office_head' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],

            'status' => [
                'required',
                Rule::in(['Active', 'Inactive']),
            ],
        ]);
    }
}