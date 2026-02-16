<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * ទាញយកបញ្ជីសិទ្ធិ (GET /api/permissions)
     */
    public function index(Request $request)
    {
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
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
            'guard_name' => 'web'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission created successfully',
            'data' => $permission
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
        $permission = Permission::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);

        $permission->update([
            'name' => strtolower(str_replace(' ', '_', $request->name))
        ]);

        return response()->json(['message' => 'Updated successfully', 'data' => $permission]);
    }

    /**
     * លុបសិទ្ធិ (DELETE /api/permissions/{permission})
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}