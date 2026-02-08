<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a list of roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data'    => $roles
        ], 200);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check duplicate role name
        if (Role::where('name', $request->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Role name already exists'
            ], 409);
        }

        // Create role
        $role = Role::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data'    => $role
        ], 201);
    }

    /**
     * Display the specified role
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $role
        ], 200);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check duplicate role name (ignore current role)
        if (
            Role::where('name', $request->name)
                ->where('id', '!=', $role->id)
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Role name already exists'
            ], 409); // Conflict
        }

        // Update role
        $role->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data'    => $role
        ], 200);
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ], 200);
    }
}