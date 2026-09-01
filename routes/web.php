<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeeImportController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\LeaveRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated BEMS Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'active',
])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Employee and Department Head Self-Service
    |--------------------------------------------------------------------------
    |
    | Regular employees and department heads can only access their own
    | employee record, leave applications and documents.
    |
    */

    Route::middleware(
        'role:employee,department_head'
    )->group(function () {
        Route::get(
            '/my-employee-record',
            [EmployeePortalController::class, 'profile']
        )->name('employee-portal.index');

        Route::get(
            '/my-leave-records',
            [EmployeePortalController::class, 'leaves']
        )->name('employee-portal.leaves');

        Route::post(
            '/my-leave-records',
            [EmployeePortalController::class, 'storeLeave']
        )->name('employee-portal.leaves.store');

        Route::patch(
            '/my-leave-records/{leaveRecord}/cancel',
            [EmployeePortalController::class, 'cancelLeave']
        )->name('employee-portal.leaves.cancel');

        Route::get(
            '/my-documents',
            [EmployeePortalController::class, 'documents']
        )->name('employee-portal.documents');

        Route::get(
            '/my-documents/{document}/download',
            [EmployeePortalController::class, 'downloadDocument']
        )->name('employee-portal.documents.download');
    });

    /*
    |--------------------------------------------------------------------------
    | Employee and Department Viewing
    |--------------------------------------------------------------------------
    |
    | Department heads can view employees only in their own department.
    | EmployeeController applies the department restriction.
    |
    */

    Route::middleware(
        'role:admin,hrmo_staff,department_head'
    )->group(function () {
        Route::get(
            '/employees',
            [EmployeeController::class, 'index']
        )->name('employees.index');

        Route::get(
            '/employees/{employee}',
            [EmployeeController::class, 'show']
        )->name('employees.show');

        Route::get(
            '/departments',
            [DepartmentController::class, 'index']
        )->name('departments.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Administrator and HRMO Staff Routes
    |--------------------------------------------------------------------------
    |
    | Only administrators and HRMO staff can create, import, update or delete
    | employee records and access organization-wide HRMO modules.
    |
    */

    Route::middleware(
        'role:admin,hrmo_staff'
    )->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Employee Excel Import
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/employees-import/template',
            [EmployeeImportController::class, 'template']
        )->name('employees.import.template');

        Route::post(
            '/employees-import',
            [EmployeeImportController::class, 'import']
        )->name('employees.import');

        /*
        |--------------------------------------------------------------------------
        | Employee Management
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/employees',
            [EmployeeController::class, 'store']
        )->name('employees.store');

        Route::put(
            '/employees/{employee}',
            [EmployeeController::class, 'update']
        )->name('employees.update');

        Route::delete(
            '/employees/{employee}',
            [EmployeeController::class, 'destroy']
        )->name('employees.destroy');

        /*
        |--------------------------------------------------------------------------
        | Department Management
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/departments',
            [DepartmentController::class, 'store']
        )->name('departments.store');

        Route::put(
            '/departments/{department}',
            [DepartmentController::class, 'update']
        )->name('departments.update');

        Route::delete(
            '/departments/{department}',
            [DepartmentController::class, 'destroy']
        )->name('departments.destroy');

        /*
        |--------------------------------------------------------------------------
        | Organization-Wide Leave Monitoring
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/leave-records',
            [LeaveRecordController::class, 'index']
        )->name('leave-records.index');

        Route::post(
            '/leave-records',
            [LeaveRecordController::class, 'store']
        )->name('leave-records.store');

        Route::put(
            '/leave-records/{leaveRecord}',
            [LeaveRecordController::class, 'update']
        )->name('leave-records.update');

        Route::patch(
            '/leave-records/{leaveRecord}/status',
            [LeaveRecordController::class, 'updateStatus']
        )->name('leave-records.status');

        Route::delete(
            '/leave-records/{leaveRecord}',
            [LeaveRecordController::class, 'destroy']
        )->name('leave-records.destroy');

        /*
        |--------------------------------------------------------------------------
        | Organization-Wide Document Tracker
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/documents',
            [EmployeeDocumentController::class, 'index']
        )->name('documents.index');

        Route::post(
            '/documents',
            [EmployeeDocumentController::class, 'store']
        )->name('documents.store');

        Route::post(
            '/documents/{document}/update',
            [EmployeeDocumentController::class, 'update']
        )->name('documents.update');

        Route::get(
            '/documents/{document}/download',
            [EmployeeDocumentController::class, 'download']
        )->name('documents.download');

        Route::delete(
            '/documents/{document}',
            [EmployeeDocumentController::class, 'destroy']
        )->name('documents.destroy');

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports',
            [ReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            '/reports/employees/export',
            [ReportController::class, 'exportEmployees']
        )->name('reports.employees.export');
    });

    /*
    |--------------------------------------------------------------------------
    | Administrator-Only User Management
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        'role:admin'
    )->group(function () {
        Route::get(
            '/users',
            [UserManagementController::class, 'index']
        )->name('users.index');

        Route::post(
            '/users',
            [UserManagementController::class, 'store']
        )->name('users.store');

        Route::put(
            '/users/{user}',
            [UserManagementController::class, 'update']
        )->name('users.update');

        Route::patch(
            '/users/{user}/toggle',
            [UserManagementController::class, 'toggle']
        )->name('users.toggle');
    });

    /*
    |--------------------------------------------------------------------------
    | User Account Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';