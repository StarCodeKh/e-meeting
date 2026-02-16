<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\ScheduleTypeController;

    // Endpoint to get RSA public key
    Route::get('/public-key', function () {
        return response()->json([
            'public_key' => file_get_contents(storage_path('rsa/private.pem'))
        ]);
    });

    // Route សម្រាប់ទាញយក Types និង Priorities ក្នុងពេលតែមួយ
    Route::get('/schedule-form-options', [ScheduleTypeController::class, 'index']);
    // Route សម្រាប់ Update ពណ៌តាម slug
    Route::post('/schedule-types/update', [ScheduleTypeController::class, 'updateTypeColor']);
    Route::post('/priorities/update', [ScheduleTypeController::class, 'updatePriority']);

    // Authentication API
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    // public link
    Route::get('schedules/public', [ScheduleController::class, 'schedulesPublic']);

    Route::middleware('auth:api')->group(function () {
        // User Management API
        Route::get('user-profile', [UserController::class, 'profile']);
        Route::apiResource('users', UserController::class);
        
        // Schedule API
        Route::get('schedules/calendar', [ScheduleController::class, 'calendarShow']);
        Route::get('schedules/all', [ScheduleController::class, 'scheduleAll']);
        Route::apiResource('schedules', ScheduleController::class);

        // Roles
        Route::apiResource('roles', RoleController::class)->except(['create', 'edit']);
        // Permissions
        Route::apiResource('permissions', PermissionController::class)->except(['create', 'edit']);

        // Role-Permission Management
        Route::post('/roles/{role}/permissions/toggle', [RolePermissionController::class, 'togglePermission']);
        Route::post('/roles/{role}/modules/toggle', [RolePermissionController::class, 'toggleModule']);
        Route::get('/modules-actions', [RolePermissionController::class, 'getModulesAndActions']);

        // Modules
        Route::apiResource('modules', ModuleController::class);
    });


