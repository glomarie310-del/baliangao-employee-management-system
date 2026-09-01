<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'employment_status',
        'date_hired',
        'gsis_number',
        'pagibig_number',
        'philhealth_number',
        'tin_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'date_hired' => 'date',
            'salary_grade' => 'integer',
            'step' => 'integer',
        ];
    }

    protected $appends = [
        'full_name',
    ];

    public function getFullNameAttribute(): string
    {
        $middleInitial = $this->middle_name
            ? ' '.strtoupper(substr($this->middle_name, 0, 1)).'.'
            : '';

        $suffix = $this->suffix ? ' '.$this->suffix : '';

        return trim(
            $this->first_name.
            $middleInitial.
            ' '.
            $this->last_name.
            $suffix
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function leaveRecords(): HasMany
    {
        return $this->hasMany(LeaveRecord::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}