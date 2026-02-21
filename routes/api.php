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
use App\Http\Controllers\Api\NotificationSettingController;
use App\Http\Controllers\Api\AnalyticsController;

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
    
    // Middleware Admin
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/schedule-types/update', [ScheduleTypeController::class, 'updateTypeColor']);
        Route::post('/priorities/update', [ScheduleTypeController::class, 'updatePriority']);
    });

    // Authentication API
    Route::prefix('auth')->group(function () {
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
            Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
            // Route សម្រាប់ Update ព័ត៌មាន
            Route::post('update-profile', [AuthController::class, 'updateProfile']);
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
        Route::get('participants', [ScheduleController::class, 'participants']);
        Route::apiResource('schedules', ScheduleController::class);

        // Permissions
        Route::apiResource('permissions', PermissionController::class);
        // Roles
        Route::apiResource('roles', RoleController::class);
        // Modules
        Route::apiResource('modules', ModuleController::class);

        // API សម្រាប់គ្រប់គ្រង Telegram Settings
        Route::get('/notification-settings', [NotificationSettingController::class, 'index']);
        Route::post('/notification-settings', [NotificationSettingController::class, 'update']);

    });

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/analytics/summary', [AnalyticsController::class, 'getSummary']);
        Route::get('/analytics/chart-data', [AnalyticsController::class, 'getChartData']);
        Route::get('/analytics/export/{type}', [AnalyticsController::class, 'exportFile']);
    });


