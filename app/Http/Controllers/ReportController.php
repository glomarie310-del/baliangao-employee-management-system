<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\LeaveRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $department = $request->input('department');
        $status = $request->input('status');

        $employeeQuery = $this->employeeQuery($department, $status);

        return Inertia::render('Reports/Index', [
            'employees' => (clone $employeeQuery)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),

            'departments' => Department::query()
                ->orderBy('name')
                ->pluck('name'),

            'departmentSummary' => Employee::query()
                ->selectRaw('department, COUNT(*) as employee_count')
                ->groupBy('department')
                ->orderBy('department')
                ->get(),

            'leaveSummary' => [
                'total' => LeaveRecord::count(),
                'pending' => LeaveRecord::where('status', 'Pending')->count(),
                'approved' => LeaveRecord::where('status', 'Approved')->count(),
                'rejected' => LeaveRecord::where('status', 'Rejected')->count(),
                'cancelled' => LeaveRecord::where('status', 'Cancelled')->count(),
            ],

            'documentSummary' => [
                'total' => EmployeeDocument::count(),
                'valid' => EmployeeDocument::where('status', 'Valid')->count(),
                'expiring' => EmployeeDocument::where(
                    'status',
                    'Expiring Soon'
                )->count(),
                'expired' => EmployeeDocument::where('status', 'Expired')->count(),
            ],

            'employeeSummary' => [
                'total' => Employee::count(),
                'active' => Employee::where('status', 'Active')->count(),
                'inactive' => Employee::where('status', 'Inactive')->count(),
                'retired' => Employee::where('status', 'Retired')->count(),
                'separated' => Employee::where('status', 'Separated')->count(),
                'male' => Employee::where('sex', 'Male')->count(),
                'female' => Employee::where('sex', 'Female')->count(),
            ],

            'expiringDocuments' => EmployeeDocument::query()
                ->with('employee:id,employee_number,first_name,middle_name,last_name,suffix')
                ->whereIn('status', ['Expiring Soon', 'Expired'])
                ->orderBy('expiration_date')
                ->get(),

            'recentLeaves' => LeaveRecord::query()
                ->with('employee:id,employee_number,first_name,middle_name,last_name,suffix,department')
                ->latest('date_filed')
                ->limit(50)
                ->get(),

            'filters' => [
                'department' => $department,
                'status' => $status,
            ],
        ]);
    }

    public function exportEmployees(Request $request): StreamedResponse
    {
        $department = $request->input('department');
        $status = $request->input('status');

        $employees = $this->employeeQuery($department, $status)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $filename = 'bems-employee-masterlist-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            function () use ($employees) {
                $file = fopen('php://output', 'w');

                // UTF-8 BOM for Microsoft Excel
                fwrite($file, "\xEF\xBB\xBF");

                fputcsv($file, [
                    'Employee Number',
                    'Last Name',
                    'First Name',
                    'Middle Name',
                    'Suffix',
                    'Sex',
                    'Birth Date',
                    'Civil Status',
                    'Department/Office',
                    'Position',
                    'Salary Grade',
                    'Step',
                    'Employment Status',
                    'Date Hired',
                    'Record Status',
                    'Contact Number',
                    'Email',
                    'Address',
                    'GSIS Number',
                    'Pag-IBIG Number',
                    'PhilHealth Number',
                    'TIN',
                ]);

                foreach ($employees as $employee) {
                    fputcsv($file, [
                        $this->safeCsv($employee->employee_number),
                        $this->safeCsv($employee->last_name),
                        $this->safeCsv($employee->first_name),
                        $this->safeCsv($employee->middle_name),
                        $this->safeCsv($employee->suffix),
                        $employee->sex,
                        $employee->birth_date?->format('Y-m-d'),
                        $employee->civil_status,
                        $this->safeCsv($employee->department),
                        $this->safeCsv($employee->position),
                        $employee->salary_grade,
                        $employee->step,
                        $employee->employment_status,
                        $employee->date_hired?->format('Y-m-d'),
                        $employee->status,
                        $this->safeCsv($employee->contact_number),
                        $this->safeCsv($employee->email),
                        $this->safeCsv($employee->address),
                        $this->safeCsv($employee->gsis_number),
                        $this->safeCsv($employee->pagibig_number),
                        $this->safeCsv($employee->philhealth_number),
                        $this->safeCsv($employee->tin_number),
                    ]);
                }

                fclose($file);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    private function employeeQuery(
        ?string $department,
        ?string $status
    ): Builder {
        return Employee::query()
            ->when($department, function (Builder $query) use ($department) {
                $query->where('department', $department);
            })
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            });
    }

    private function safeCsv(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if (
            str_starts_with($value, '=') ||
            str_starts_with($value, '+') ||
            str_starts_with($value, '-') ||
            str_starts_with($value, '@')
        ) {
            return "'".$value;
        }

        return $value;
    }
}