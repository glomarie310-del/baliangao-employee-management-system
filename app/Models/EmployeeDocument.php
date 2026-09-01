<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_type',
        'title',
        'reference_number',
        'issuing_agency',
        'date_issued',
        'expiration_date',
        'status',
        'file_path',
        'original_file_name',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date_issued' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}