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
        $path = storage_path('rsa/public.pem');
        
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found at ' . $path], 404);
        }

        return response()->json([
            'public_key' => file_get_contents($path)
        ]);
    });

    // Route សម្រាប់ទាញយក Types និង Priorities ក្នុងពេលតែមួយ
    Route::get('/schedule-form-options', [ScheduleTypeController::class, 'index']);
    Route::post('/schedule-types/update', [ScheduleTypeController::class, 'updateTypeColor']);
    Route::post('/priorities/update', [ScheduleTypeController::class, 'updatePriority']);

    // Authentication API
    // Route::prefix('auth')->group(function () {
    //     Route::post('register', [AuthController::class, 'register']);
    //     Route::post('login', [AuthController::class, 'login']);
    //     Route::middleware('auth:api')->group(function () {
    //         // គ្រប់គ្រងគណនី និងសុវត្ថិភាព
    //         Route::post('logout', [AuthController::class, 'logout']);
    //         Route::post('refresh', [AuthController::class, 'refresh']);
    //         Route::post('change-password', [AuthController::class, 'changePassword']);

    //         // ទាញយកព័ត៌មាន User ដែលកំពុង Login
    //         Route::get('me', function () {
    //             return response()->json(auth()->user()->load('roles.permissions'));
    //         })->name('api.auth.me');
    //     });
    // });

    Route::prefix('auth')->group(function () {
        // ១. ដាក់នៅទីនេះ ដើម្បីឱ្យ Vue អាចទាញយកបានដោយសេរី (Method: GET)
        Route::get('public-key', function () {
            return response()->json([
                'public_key' => file_get_contents(storage_path('rsa/public.pem'))
            ]);
        });

        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {
            // គ្រប់គ្រងគណនី និងសុវត្ថិភាព (Method: POST)
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            
            // ត្រូវប្រាកដថា Controller មាន function changePassword
            Route::post('change-password', [AuthController::class, 'changePassword']);

            // ទាញយកព័ត៌មាន User ដែលកំពុង Login
            Route::get('me', function () {
                return response()->json(auth()->user()->load('roles.permissions'));
            })->name('api.auth.me');
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

        // Permissions
        Route::apiResource('permissions', PermissionController::class);
        // Roles
        Route::apiResource('roles', RoleController::class);
        // Modules
        Route::apiResource('modules', ModuleController::class);
    });


