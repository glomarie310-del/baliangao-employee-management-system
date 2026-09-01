<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $documentType = $request->input('document_type');
        $status = $request->input('status');

        $this->refreshStatuses();

        $documents = EmployeeDocument::query()
            ->with('employee:id,employee_number,first_name,middle_name,last_name,suffix,department')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($documentQuery) use ($search) {
                    $documentQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('issuing_agency', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery
                                ->where('employee_number', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($documentType, function ($query) use ($documentType) {
                $query->where('document_type', $documentType);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,

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
                ]),

            'filters' => [
                'search' => $search,
                'document_type' => $documentType,
                'status' => $status,
            ],

            'summary' => [
                'total' => EmployeeDocument::count(),
                'valid' => EmployeeDocument::where('status', 'Valid')->count(),
                'expiring' => EmployeeDocument::where(
                    'status',
                    'Expiring Soon'
                )->count(),
                'expired' => EmployeeDocument::where('status', 'Expired')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDocument($request);

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');

            $validated['file_path'] = $file->store(
                'employee-documents',
                'local'
            );

            $validated['original_file_name'] = $file->getClientOriginalName();
        }

        $validated['status'] = $this->determineStatus(
            $validated['expiration_date'] ?? null
        );

        unset($validated['document_file']);

        EmployeeDocument::create($validated);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Employee document uploaded successfully.');
    }

    public function update(
        Request $request,
        EmployeeDocument $document
    ): RedirectResponse {
        $validated = $this->validateDocument($request);

        if ($request->hasFile('document_file')) {
            if ($document->file_path) {
                Storage::disk('local')->delete($document->file_path);
            }

            $file = $request->file('document_file');

            $validated['file_path'] = $file->store(
                'employee-documents',
                'local'
            );

            $validated['original_file_name'] = $file->getClientOriginalName();
        }

        $validated['status'] = $this->determineStatus(
            $validated['expiration_date'] ?? null
        );

        unset($validated['document_file']);

        $document->update($validated);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Employee document updated successfully.');
    }

    public function download(
        EmployeeDocument $document
    ): BinaryFileResponse|RedirectResponse {
        if (
            ! $document->file_path ||
            ! Storage::disk('local')->exists($document->file_path)
        ) {
            return redirect()
                ->route('documents.index')
                ->with('error', 'The uploaded file could not be found.');
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_file_name ?? basename($document->file_path)
        );
    }

    public function destroy(
        EmployeeDocument $document
    ): RedirectResponse {
        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Employee document deleted successfully.');
    }

    private function validateDocument(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],

            'document_type' => [
                'required',
                Rule::in([
                    'Personal Data Sheet',
                    'Appointment Paper',
                    'Oath of Office',
                    'Position Description Form',
                    'Medical Certificate',
                    'NBI Clearance',
                    'Police Clearance',
                    'Birth Certificate',
                    'Marriage Certificate',
                    'Diploma',
                    'Transcript of Records',
                    'Certificate of Eligibility',
                    'Training Certificate',
                    'Service Record',
                    'SALN',
                    'IPCR',
                    'Other',
                ]),
            ],

            'title' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'issuing_agency' => ['nullable', 'string', 'max:255'],
            'date_issued' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],

            'document_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240',
            ],
        ]);
    }

    private function determineStatus(?string $expirationDate): string
    {
        if (! $expirationDate) {
            return 'Valid';
        }

        $expiration = now()->parse($expirationDate)->startOfDay();
        $today = now()->startOfDay();

        if ($expiration->isBefore($today)) {
            return 'Expired';
        }

        if ($expiration->lessThanOrEqualTo($today->copy()->addDays(30))) {
            return 'Expiring Soon';
        }

        return 'Valid';
    }

    private function refreshStatuses(): void
    {
        EmployeeDocument::query()
            ->select(['id', 'expiration_date', 'status'])
            ->get()
            ->each(function (EmployeeDocument $document) {
                $status = $this->determineStatus(
                    $document->expiration_date?->format('Y-m-d')
                );

                if ($document->status !== $status) {
                    $document->update(['status' => $status]);
                }
            });
    }
}