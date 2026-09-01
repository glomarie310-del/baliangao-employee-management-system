<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'date_filed',
        'date_from',
        'date_to',
        'number_of_days',
        'reason',
        'with_pay',
        'status',
        'remarks',
        'approved_by',
        'date_approved',
    ];

    protected function casts(): array
    {
        return [
            'date_filed' => 'date',
            'date_from' => 'date',
            'date_to' => 'date',
            'date_approved' => 'date',
            'number_of_days' => 'decimal:2',
            'with_pay' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}