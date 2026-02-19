<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Module;

class PermissionController extends Controller
{
    /**
     * ទាញយកបញ្ជីសិទ្ធិ (GET /api/permissions)
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិមើលបញ្ជីសិទ្ធិប្រើប្រាស់ឡើយ!'
            ], 403);
        }
        
        $permissions = Permission::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 5));

        return response()->json($permissions);
    }
    /**
     * បង្កើតសិទ្ធិថ្មី (POST /api/permissions)
     */

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ក្នុងការបង្កើតសិទ្ធិថ្មីឡើយ!'
            ], 403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('permissions')->where(function ($query) {
                    return $query->where('guard_name', 'api');
                }),
            ],
        ]);

        $formattedName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $request->name)));

        $permission = Permission::create([
            'name'       => $formattedName,
            'guard_name' => 'api'
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'បង្កើតសិទ្ធិជោគជ័យ',
            'data'    => $permission
        ], 201);
    }

    /**
     * បង្ហាញសិទ្ធិមួយតាម ID (GET /api/permissions/{permission})
     */
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json(['data' => $permission]);
    }

    /**
     * កែប្រែសិទ្ធិ (PUT /api/permissions/{permission})
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ក្នុងការកែប្រែសិទ្ធិឡើយ!'
            ], 403);
        }

        $permission = Permission::findOrFail($id);
        $oldName = $permission->name;

        $request->validate([
            'name' => ['required','string',Rule::unique('permissions')->ignore($id)->where('guard_name', 'api')
            ],
        ]);

        $newName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $request->name)));

        $permission->update([
            'name' => $newName
        ]);

        Module::where('permission_name', $oldName)->update(['permission_name' => $newName]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'status' => 'success',
            'message' => 'កែប្រែសិទ្ធិ និងបច្ចុប្បន្នភាពម៉ូឌុលជោគជ័យ',
            'data' => $permission
        ]);
    }

    /**
     * លុបសិទ្ធិ (DELETE /api/permissions/{permission})
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ក្នុងការលុបទិន្នន័យនេះទេ!'
            ], 403);
        }
        
        $permission = Permission::findOrFail($id);
        $permissionName = $permission->name;
        Module::where('permission_name', $permissionName)->update(['permission_name' => null]);

        $permission->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'status' => 'success',
            'message' => 'លុបសិទ្ធិ និងសម្អាតទិន្នន័យម៉ូឌុលជោគជ័យ'
        ]);
    }
}