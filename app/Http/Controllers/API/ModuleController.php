<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $query = Module::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('label', 'like', "%{$search}%")->orWhere('key_name', 'like', "%{$search}%");
                });
            })->orderBy('sort_order', 'asc');
        if ($request->boolean('is_menu')) {
            return $query->where('is_active', true)->get()->filter(fn($module) => empty($module->permission_name) || $user->can($module->permission_name))->values();
        }
        return $query->paginate($request->integer('per_page', 100));
    }

    /**
     * បង្កើតម៉ូឌុលថ្មី
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key_name'   => 'required|string|unique:modules,key_name',
            'label'      => 'required|string|max:255',
            'path'       => 'required|string', 
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
            'key_name'        => ['required', 'string', Rule::unique('modules')->ignore($module->id)],
            'label'           => 'required|string|max:255',
            'path'            => 'required|string', 
            'permission_name' => 'nullable|string',
            'sort_order'      => 'nullable|integer',
            'is_active'       => 'nullable|boolean',
            'icon'            => 'nullable|string',
            'description'     => 'nullable|string'
        ]);

        $module->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'កែប្រែម៉ូឌុលជោគជ័យ',
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