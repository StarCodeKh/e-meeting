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

    // Endpoint to get RSA public key
    Route::get('/public-key', function () {
        return response()->json([
            'public_key' => file_get_contents(storage_path('rsa/private.pem'))
        ]);
    });
    
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
        Route::apiResource('users', UserController::class);
        // Schedule API
        Route::get('schedules/calendar', [ScheduleController::class, 'calendarShow']);
        Route::apiResource('schedules', ScheduleController::class);

        // Roles
        Route::apiResource('roles', RoleController::class)->except(['create', 'edit']);
        // Permissions
        Route::apiResource('permissions', PermissionController::class)->except(['create', 'edit']);

        // Role-Permission Management
        Route::post('/roles/{role}/permissions/toggle', [RolePermissionController::class, 'togglePermission']);
        Route::post('/roles/{role}/modules/toggle', [RolePermissionController::class, 'toggleModule']);
        Route::get('/modules-actions', [RolePermissionController::class, 'getModulesAndActions']);

        // Modules and Actions
        Route::get('/modules', [ModuleController::class, 'index']);
        Route::post('/modules', [ModuleController::class, 'store']);
        Route::get('/actions', [ActionController::class, 'index']);
        Route::post('/actions', [ActionController::class, 'store']);
    });


