<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesTemplateExport;
use App\Imports\EmployeesImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class EmployeeImportController extends Controller
{
    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new EmployeesTemplateExport(),
            'bems-employee-import-template.xlsx'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ]);

        $import = new EmployeesImport();

        try {
            Excel::import($import, $request->file('excel_file'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('employees.index')
                ->with(
                    'error',
                    'The Excel file could not be imported. Check that it uses the official BEMS template.'
                );
        }

        $message = "{$import->imported} employee(s) imported successfully.";

        if ($import->skipped > 0) {
            $message .= " {$import->skipped} row(s) were skipped.";
        }

        return redirect()
            ->route('employees.index')
            ->with('success', $message)
            ->with(
                'import_errors',
                array_slice($import->errors, 0, 20)
            );
    }
}