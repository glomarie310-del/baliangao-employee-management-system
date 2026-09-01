<?php

namespace App\Imports;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class EmployeesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;

    public int $skipped = 0;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;

            $data = [
                'employee_number' => trim((string) $row['employee_number']),
                'first_name' => trim((string) $row['first_name']),
                'middle_name' => $this->nullableString($row['middle_name'] ?? null),
                'last_name' => trim((string) $row['last_name']),
                'suffix' => $this->nullableString($row['suffix'] ?? null),
                'sex' => ucfirst(strtolower(trim((string) $row['sex']))),
                'birth_date' => $this->parseDate($row['birth_date'] ?? null),
                'civil_status' => $this->nullableTitleCase(
                    $row['civil_status'] ?? null
                ),
                'contact_number' => $this->nullableString(
                    $row['contact_number'] ?? null
                ),
                'email' => $this->nullableString($row['email'] ?? null),
                'address' => $this->nullableString($row['address'] ?? null),
                'department' => trim((string) $row['department']),
                'position' => trim((string) $row['position']),
                'salary_grade' => $this->nullableInteger(
                    $row['salary_grade'] ?? null
                ),
                'step' => $this->nullableInteger($row['step'] ?? null),
                'employment_status' => 'Regular',
                'date_hired' => $this->parseDate($row['date_hired'] ?? null),
                'gsis_number' => $this->nullableString(
                    $row['gsis_number'] ?? null
                ),
                'pagibig_number' => $this->nullableString(
                    $row['pagibig_number'] ?? null
                ),
                'philhealth_number' => $this->nullableString(
                    $row['philhealth_number'] ?? null
                ),
                'tin_number' => $this->nullableString(
                    $row['tin_number'] ?? null
                ),
                'status' => $this->nullableTitleCase(
                    $row['status'] ?? 'Active'
                ) ?? 'Active',
            ];

            $validator = Validator::make($data, [
                'employee_number' => [
                    'required',
                    'string',
                    'max:50',
                ],
                'first_name' => ['required', 'string', 'max:100'],
                'middle_name' => ['nullable', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'suffix' => ['nullable', 'string', 'max:20'],
                'sex' => ['required', Rule::in(['Male', 'Female'])],
                'birth_date' => ['nullable', 'date'],
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
                'contact_number' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'address' => ['nullable', 'string', 'max:1000'],
                'department' => ['required', 'string', 'max:255'],
                'position' => ['required', 'string', 'max:255'],
                'salary_grade' => ['nullable', 'integer', 'between:1,33'],
                'step' => ['nullable', 'integer', 'between:1,8'],
                'date_hired' => ['nullable', 'date'],
                'gsis_number' => ['nullable', 'string', 'max:50'],
                'pagibig_number' => ['nullable', 'string', 'max:50'],
                'philhealth_number' => ['nullable', 'string', 'max:50'],
                'tin_number' => ['nullable', 'string', 'max:50'],
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

            if ($validator->fails()) {
                $this->skipped++;

                $this->errors[] = sprintf(
                    'Row %d: %s',
                    $excelRow,
                    implode(' ', $validator->errors()->all())
                );

                continue;
            }

            if (
                Employee::where(
                    'employee_number',
                    $data['employee_number']
                )->exists()
            ) {
                $this->skipped++;

                $this->errors[] =
                    "Row {$excelRow}: Employee number ".
                    "{$data['employee_number']} already exists.";

                continue;
            }

            Employee::create($validator->validated());

            $this->imported++;
        }
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function nullableTitleCase(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        return $value ? ucwords(strtolower($value)) : null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}