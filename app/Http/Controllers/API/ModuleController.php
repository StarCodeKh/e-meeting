<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    /**
     * ទាញយកទិន្នន័យទាំងអស់ (តម្រៀបតាមលំដាប់ sort_order)
     */
    public function index(Request $request): JsonResponse
    {
        $modules = Module::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('sort_order', 'asc')
            ->paginate($request->integer('per_page', 5));

        return response()->json($modules);
    }
    /**
     * បង្កើតម៉ូឌុលថ្មី
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key_name'   => 'required|string|unique:modules,key_name',
            'label'      => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
            'icon'       => 'nullable|string',
            'description'=> 'nullable|string'
        ]);

        $module = Module::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Module created successfully',
            'data'    => $module
        ], 201);
    }

    /**
     * កែប្រែទិន្នន័យ (Update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        $validated = $request->validate([
            'key_name'   => ['required', 'string', Rule::unique('modules')->ignore($module->id)],
            'label'      => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
            'icon'       => 'nullable|string',
            'description'=> 'nullable|string'
        ]);

        $module->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Module updated successfully',
            'data'    => $module
        ]);
    }

    /**
     * លុបទិន្នន័យ (Delete)
     */
    public function destroy($id): JsonResponse
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return response()->json([
            'success' => true,
            'message' => 'Module deleted successfully'
        ]);
    }
}