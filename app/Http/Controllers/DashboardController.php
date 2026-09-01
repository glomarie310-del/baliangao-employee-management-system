<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $user = $request->user();

        $stats = [
            'totalEmployees' => 0,
            'activeEmployees' => 0,
            'hrmoStaff' => 0,
            'inactiveAccounts' => 0,
        ];

        $recentUsers = [];
        $departmentName = null;
        $hasLinkedEmployee = false;

        /*
         * Administrator and HRMO staff:
         * organization-wide statistics.
         */
        if (
            in_array(
                $user->role,
                [
                    'admin',
                    'hrmo_staff',
                ],
                true
            )
        ) {
            $stats = [
                'totalEmployees' =>
                    Employee::count(),

                'activeEmployees' =>
                    Employee::where(
                        'status',
                        'Active'
                    )->count(),

                'hrmoStaff' =>
                    User::whereIn(
                        'role',
                        [
                            'admin',
                            'hrmo_staff',
                        ]
                    )->count(),

                'inactiveAccounts' =>
                    User::where(
                        'is_active',
                        false
                    )->count(),
            ];
        }

        /*
         * Department head:
         * statistics limited to the linked employee's
         * department.
         */
        if ($user->role === 'department_head') {
            $departmentHeadRecord = Employee::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->first();

            if ($departmentHeadRecord) {
                $hasLinkedEmployee = true;

                $departmentName =
                    $departmentHeadRecord->department;

                $departmentEmployees =
                    Employee::query()
                        ->where(
                            'department',
                            $departmentName
                        );

                $stats = [
                    'totalEmployees' =>
                        (clone $departmentEmployees)
                            ->count(),

                    'activeEmployees' =>
                        (clone $departmentEmployees)
                            ->where(
                                'status',
                                'Active'
                            )
                            ->count(),

                    'hrmoStaff' => 0,

                    'inactiveAccounts' => 0,
                ];
            }
        }

        /*
         * Regular employee:
         * confirm whether their account is linked
         * to an employee record.
         */
        if ($user->role === 'employee') {
            $hasLinkedEmployee = Employee::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->exists();
        }

        /*
         * Recent account information is restricted
         * to administrators.
         */
        if ($user->role === 'admin') {
            $recentUsers = User::query()
                ->select([
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                    'created_at',
                ])
                ->latest()
                ->limit(5)
                ->get();
        }

        return Inertia::render(
            'Dashboard',
            [
                'stats' => $stats,

                'recentUsers' => $recentUsers,

                'departmentName' =>
                    $departmentName,

                'hasLinkedEmployee' =>
                    $hasLinkedEmployee,
            ]
        );
    }
}