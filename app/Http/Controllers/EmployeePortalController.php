<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\LeaveRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeePortalController extends Controller
{
    public function profile(Request $request): Response
    {
        return $this->renderPortal($request, 'profile');
    }

    public function leaves(Request $request): Response
    {
        return $this->renderPortal($request, 'leaves');
    }

    public function documents(Request $request): Response
    {
        return $this->renderPortal($request, 'documents');
    }

    public function storeLeave(
        Request $request
    ): RedirectResponse {
        $employee = $this->getLinkedEmployee($request);

        if (! $employee) {
            return back()->with(
                'error',
                'Your account is not linked to an employee record.'
            );
        }

        $validated = $request->validate([
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
            'date_from' => [
                'required',
                'date',
            ],
            'date_to' => [
                'required',
                'date',
                'after_or_equal:date_from',
            ],
            'number_of_days' => [
                'required',
                'numeric',
                'min:0.5',
            ],
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
            'with_pay' => [
                'required',
                'boolean',
            ],
        ]);

        LeaveRecord::create([
            'employee_id' => $employee->id,
            'leave_type' => $validated['leave_type'],
            'date_filed' => now()->toDateString(),
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'number_of_days' => $validated['number_of_days'],
            'reason' => $validated['reason'],
            'with_pay' => $validated['with_pay'],
            'status' => 'Pending',
            'remarks' => null,
            'approved_by' => null,
            'date_approved' => null,
        ]);

        return redirect()
            ->route('employee-portal.leaves')
            ->with(
                'success',
                'Your leave application was submitted successfully.'
            );
    }

    public function cancelLeave(
        Request $request,
        LeaveRecord $leaveRecord
    ): RedirectResponse {
        $employee = $this->getLinkedEmployee($request);

        if (
            ! $employee ||
            $leaveRecord->employee_id !== $employee->id
        ) {
            abort(403);
        }

        if ($leaveRecord->status !== 'Pending') {
            return back()->with(
                'error',
                'Only pending leave applications can be cancelled.'
            );
        }

        $leaveRecord->update([
            'status' => 'Cancelled',
        ]);

        return redirect()
            ->route('employee-portal.leaves')
            ->with(
                'success',
                'Leave application cancelled successfully.'
            );
    }

    public function downloadDocument(
        Request $request,
        EmployeeDocument $document
    ): StreamedResponse|RedirectResponse {
        $employee = $this->getLinkedEmployee($request);

        if (
            ! $employee ||
            $document->employee_id !== $employee->id
        ) {
            abort(403);
        }

        if (
            ! $document->file_path ||
            ! Storage::disk('local')->exists(
                $document->file_path
            )
        ) {
            return back()->with(
                'error',
                'The requested document could not be found.'
            );
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_file_name ??
                basename($document->file_path)
        );
    }

    private function renderPortal(
        Request $request,
        string $activeTab
    ): Response {
        $employee = Employee::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->with([
                'leaveRecords' => function ($query) {
                    $query->latest('date_filed');
                },
                'documents' => function ($query) {
                    $query->latest();
                },
            ])
            ->first();

        return Inertia::render(
            'EmployeePortal/Index',
            [
                'employee' => $employee,
                'initialTab' => $activeTab,
            ]
        );
    }

    private function getLinkedEmployee(
        Request $request
    ): ?Employee {
        return Employee::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->first();
    }
}