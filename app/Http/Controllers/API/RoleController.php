<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * ទាញយកបញ្ជី Roles ជាមួយ Pagination & Search
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($roles);
    }

    /**
     * ទាញយក Permissions ទាំងអស់សម្រាប់បង្ហាញក្នុង Form
     */
    public function getPermissions()
    {
        return response()->json([
            'status' => 'success',
            'data' => Permission::all()
        ]);
    }

    /**
     * បង្កើត Role ថ្មី (Standard Dynamic)
     */
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'api']);

            if ($request->has('permissions')) {
                $validPermissions = Permission::whereIn('name', $request->permissions)->pluck('name')->toArray();
                $role->syncPermissions($validPermissions);
            }

            DB::commit();
            return response()->json(['message' => 'Created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Role Store Error: " . $e->getMessage());
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * បង្ហាញទិន្នន័យ Role មួយតាម ID
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $role
        ]);
    }

    /**
     * ធ្វើបច្ចុប្បន្នភាព Role (Standard Dynamic)
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::beginTransaction();
        try {
            $role->update(['name' => $request->name]);

            // syncPermissions នឹងចាត់ចែងលុបសិទ្ធិចាស់ និងបន្ថែមសិទ្ធិថ្មីដោយស្វ័យប្រវត្តិ
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'កែប្រែទិន្នន័យបានជោគជ័យ',
                'data' => $role->load('permissions')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Role Update Error: " . $e->getMessage());
            return response()->json(['message' => 'មានបញ្ហាបច្ចេកទេស: ' . $e->getMessage()], 500);
        }
    }

    /**
     * លុប Role ចេញពីប្រព័ន្ធ
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            if ($role->name === 'admin' || $role->id === 1) {
                return response()->json(['message' => 'មិនអាចលុបតួនាទី Admin បានទេ!'], 403);
            }

            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'លុបទិន្នន័យបានជោគជ័យ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'មិនអាចលុបបាន: ' . $e->getMessage()], 500);
        }
    }
}