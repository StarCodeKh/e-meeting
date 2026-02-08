<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Action;

class RolePermissionController extends Controller
{
    /**
     * Toggle a single permission for a role.
     */
    public function togglePermission(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permission' => 'required|string',
        ]);

        $permission = Permission::firstOrCreate([
            'name' => $request->input('permission'),
        ]);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        } else {
            $role->givePermissionTo($permission);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle all permissions under a module for a role.
     */
    public function toggleModule(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
        ]);

        $module = $request->input('module');

        $permissions = Permission::where('name', 'like', "$module.%")->get();

        // Check if role has all permissions in this module
        $hasAll = $permissions->every(fn ($p) => $role->hasPermissionTo($p));

        foreach ($permissions as $permission) {
            if ($hasAll) {
                $role->revokePermissionTo($permission);
            } else {
                $role->givePermissionTo($permission);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get all modules and available actions.
     */
    public function getModulesAndActions(): JsonResponse
    {
        // Adjust field names based on your Module and Action models
        $modules = Module::select('key_name as key', 'label')->orderBy('label')->get();
        $actions = Action::select('name')->orderBy('name')->pluck('name');

        return response()->json([
            'modules' => $modules,
            'actions' => $actions,
        ]);
    }
}
