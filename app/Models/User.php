<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_HRMO_STAFF = 'hrmo_staff';

    public const ROLE_DEPARTMENT_HEAD = 'department_head';

    public const ROLE_EMPLOYEE = 'employee';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isHrmoStaff(): bool
    {
        return $this->role === self::ROLE_HRMO_STAFF;
    }

    public function isDepartmentHead(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT_HEAD;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function canManageEmployees(): bool
    {
        return in_array(
            $this->role,
            [
                self::ROLE_ADMIN,
                self::ROLE_HRMO_STAFF,
            ],
            true
        );
    }
}