<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $leaveType = $request->input('leave_type');
        $status = $request->input('status');

        $leaveRecords = LeaveRecord::query()
            ->with('employee:id,employee_number,first_name,middle_name,last_name,suffix,department,position')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery
                        ->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($leaveType, function ($query) use ($leaveType) {
                $query->where('leave_type', $leaveType);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('date_filed')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('LeaveRecords/Index', [
            'leaveRecords' => $leaveRecords,

            'employees' => Employee::query()
                ->where('status', 'Active')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get([
                    'id',
                    'employee_number',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'suffix',
                    'department',
                ]),

            'filters' => [
                'search' => $search,
                'leave_type' => $leaveType,
                'status' => $status,
            ],

            'summary' => [
                'total' => LeaveRecord::count(),
                'pending' => LeaveRecord::where('status', 'Pending')->count(),
                'approved' => LeaveRecord::where('status', 'Approved')->count(),
                'rejected' => LeaveRecord::where('status', 'Rejected')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        LeaveRecord::create($this->validateRecord($request));

        return redirect()
            ->route('leave-records.index')
            ->with('success', 'Leave record created successfully.');
    }

    public function update(
        Request $request,
        LeaveRecord $leaveRecord
    ): RedirectResponse {
        $leaveRecord->update($this->validateRecord($request));

        return redirect()
            ->route('leave-records.index')
            ->with('success', 'Leave record updated successfully.');
    }

    public function updateStatus(
        Request $request,
        LeaveRecord $leaveRecord
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['Pending', 'Approved', 'Rejected', 'Cancelled']),
            ],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $leaveRecord->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,

            'approved_by' => $validated['status'] === 'Approved'
                ? $request->user()->name
                : null,

            'date_approved' => $validated['status'] === 'Approved'
                ? now()->toDateString()
                : null,
        ]);

        return redirect()
            ->route('leave-records.index')
            ->with('success', 'Leave status updated successfully.');
    }

    public function destroy(LeaveRecord $leaveRecord): RedirectResponse
    {
        $leaveRecord->delete();

        return redirect()
            ->route('leave-records.index')
            ->with('success', 'Leave record deleted successfully.');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'employee_id' => [
                'required',
                'exists:employees,id',
            ],

            'leave_type' => [
                'required',
                Rule::in([
                    'Vacation Leave',
                    'Mandatory/Forced Leave',
                    'Sick Leave',
                    'Maternity Leave',
                    'Paternity Leave',
                    'Special Privilege Leave',
                    'Solo Parent Leave',
                    'Study Leave',
                    'VAWC Leave',
                    'Rehabilitation Privilege',
                    'Special Leave Benefits for Women',
                    'Special Emergency Leave',
                    'Adoption Leave',
                    'Other',
                ]),
            ],

            'date_filed' => ['required', 'date'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'number_of_days' => ['required', 'numeric', 'min:0.5'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'with_pay' => ['required', 'boolean'],

            'status' => [
                'required',
                Rule::in(['Pending', 'Approved', 'Rejected', 'Cancelled']),
            ],

            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}