<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesTemplateExport implements
    FromArray,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'BEMS-0001',
                'Juan',
                'Santos',
                'Dela Cruz',
                '',
                'Male',
                '1990-05-20',
                'Married',
                '09123456789',
                'juan.delacruz@example.com',
                'Baliangao, Misamis Occidental',
                'Human Resource Management Office',
                'Administrative Officer II',
                15,
                1,
                '2020-01-15',
                '1234567890',
                '1234-5678-9012',
                '12-345678901-2',
                '123-456-789-000',
                'Active',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'employee_number',
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'sex',
            'birth_date',
            'civil_status',
            'contact_number',
            'email',
            'address',
            'department',
            'position',
            'salary_grade',
            'step',
            'date_hired',
            'gsis_number',
            'pagibig_number',
            'philhealth_number',
            'tin_number',
            'status',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'rgb' => '1E3A8A',
                ],
            ],
        ]);

        return [];
    }
}