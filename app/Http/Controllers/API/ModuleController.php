<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index(Request $request) 
    {
        $user = $request->user();
        
        $query = Module::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('label', 'like', "%{$search}%")
                    ->orWhere('key_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order', 'asc');

        if ($request->boolean('is_menu')) {
            $modules = $query->where('is_active', true)->get();

            if ($user->hasRole('admin')) {
                return $modules;
            }

            return $modules->filter(function($module) use ($user) {
                return empty($module->permission_name) || $user->can($module->permission_name);
            })->values();
        }

        return $query->paginate($request->integer('per_page', 10));
    }

    /**
     * បង្កើតម៉ូឌុលថ្មី
     */
    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ក្នុងការបង្កើតម៉ូឌុលថ្មីឡើយ!'
            ], 403);
        }

        $validated = $request->validate([
            'key_name'    => 'required|string|max:50|unique:modules,key_name',
            'label'       => 'required|string|max:100',
            'path'        => 'required|string', 
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
            'icon'        => 'nullable|string',
            'external'    => 'nullable|boolean',
            'description' => 'nullable|string'
        ]);

        $validated['permission_name'] = $validated['key_name'] . '_view';

        DB::beginTransaction();
        try {

            $module = Module::create($validated);

            Permission::firstOrCreate([
                'name'       => $module->permission_name,
                'guard_name' => 'api'
            ]);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'ម៉ូឌុល និងសិទ្ធិប្រើប្រាស់ត្រូវបានបង្កើតដោយជោគជ័យ!',
                'data'    => $module
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Module Store Error: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចបង្កើតម៉ូឌុលបានទេ៖ ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * កែប្រែទិន្នន័យ (Update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ក្នុងការកែប្រែម៉ូឌុលនេះទេ!'
            ], 403);
        }

        $module = Module::findOrFail($id);
        $oldPermissionName = $module->permission_name;

        $validated = $request->validate([
            'key_name'        => ['required', 'string', \Illuminate\Validation\Rule::unique('modules')->ignore($module->id)],
            'label'           => 'required|string|max:255',
            'path'            => 'required|string', 
            'permission_name' => 'nullable|string',
            'sort_order'      => 'nullable|integer',
            'is_active'       => 'nullable|boolean',
            'icon'            => 'nullable|string',
            'external'        => 'nullable|boolean',
            'description'     => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $module->update($validated);

            if (!empty($module->permission_name) && $oldPermissionName !== $module->permission_name) {
                
                $spatiePermission = Permission::where('name', $oldPermissionName)->where('guard_name', 'api')->first();

                if ($spatiePermission) {
                    $spatiePermission->update(['name' => $module->permission_name]);
                } else {
                    Permission::firstOrCreate([
                        'name'       => $module->permission_name,
                        'guard_name' => 'api'
                    ]);
                }
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'កែប្រែម៉ូឌុល និងសិទ្ធិបានជោគជ័យ!',
                'data'    => $module
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Module Update Error: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស៖ ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * លុបទិន្នន័យ (Delete)
     */
    public function destroy($id): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ក្នុងការលុបម៉ូឌុលនេះទេ!'
            ], 403);
        }

        $module = Module::findOrFail($id);
        $permissionName = $module->permission_name;

        DB::beginTransaction();
        try {

            $module->delete();

            if ($permissionName) {
                Permission::where('name', $permissionName)->where('guard_name', 'api')->delete();
                
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'លុបម៉ូឌុល និងសិទ្ធិដែលពាក់ព័ន្ធបានជោគជ័យ!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Module Delete Error: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស៖ ' . $e->getMessage()
            ], 500);
        }
    }
}