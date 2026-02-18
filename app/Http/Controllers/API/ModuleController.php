<?php

namespace App\Http\Controllers\Api;

use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
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

        // ករណីទាញយកសម្រាប់បង្ហាញជា Menu
        if ($request->boolean('is_menu')) {
            $modules = $query->where('is_active', true)->get();

            // ប្រសិនបើជា ADMIN ឱ្យឃើញគ្រប់ Module ទាំងអស់តែម្តង (Performance Boost)
            if ($user->hasRole('ADMIN')) {
                return $modules;
            }

            // សម្រាប់ User ធម្មតា ចម្រាញ់តាម Permission ដែលគាត់មាន
            return $modules->filter(function($module) use ($user) {
                return empty($module->permission_name) || $user->can($module->permission_name);
            })->values();
        }

        // ករណីទាញយកសម្រាប់ទំព័រគ្រប់គ្រង (Management Page)
        return $query->paginate($request->integer('per_page', 10));
    }

    /**
     * បង្កើតម៉ូឌុលថ្មី
     */
    public function store(Request $request): JsonResponse
    {
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

        // ១. កំណត់យក key_name ធ្វើជា permission_name តែម្តងដើម្បីឱ្យស្រួលគ្រប់គ្រង
        $validated['permission_name'] = $validated['key_name'].'view_';

        // ២. បង្កើត Module ក្នុង Table modules
        $module = Module::create($validated);

        // ៣. បង្កើត Permission ក្នុង Spatie Table ស្វ័យប្រវត្តិ (បើមិនទាន់មាន)
        Permission::firstOrCreate([
            'name'       => $module->permission_name,
            'guard_name' => 'api'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Module និង Permission ត្រូវបានបង្កើតដោយជោគជ័យ',
            'data'    => $module
        ], 201);
    }

    /**
     * កែប្រែទិន្នន័យ (Update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $module = Module::findOrFail($id);
        
        // រក្សាទុកឈ្មោះ Permission ចាស់ទុកសម្រាប់ឆែក
        $oldPermissionName = $module->permission_name;

        $validated = $request->validate([
            'key_name'        => ['required', 'string', Rule::unique('modules')->ignore($module->id)],
            'label'           => 'required|string|max:255',
            'path'            => 'required|string', 
            'permission_name' => 'nullable|string',
            'sort_order'      => 'nullable|integer',
            'is_active'       => 'nullable|boolean',
            'icon'            => 'nullable|string',
            'external'        => 'nullable|boolean',
            'description'     => 'nullable|string'
        ]);

        // ១. កែប្រែទិន្នន័យក្នុង Table modules
        $module->update($validated);

        // ២. Logic សម្រាប់ Sync ជាមួយ Spatie Permission
        if (!empty($module->permission_name) && $oldPermissionName !== $module->permission_name) {
            
            $spatiePermission = Permission::where('name', $oldPermissionName)
                ->where('guard_name', 'api')
                ->first();

            if ($spatiePermission) {
                $spatiePermission->update(['name' => $module->permission_name]);
            } else {
                // បើអត់មានសោះ គឺបង្កើតថ្មីតែម្តង
                Permission::firstOrCreate([
                    'name'       => $module->permission_name,
                    'guard_name' => 'api'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'កែប្រែម៉ូឌុល និងសិទ្ធិជោគជ័យ',
            'data'    => $module
        ]);
    }

    /**
     * លុបទិន្នន័យ (Delete)
     */
    public function destroy($id): JsonResponse
    {
        $module = Module::findOrFail($id);

        // ១. រក្សាទុកឈ្មោះ Permission មុនពេលលុប Module
        $permissionName = $module->permission_name;

        // ២. លុប Module ចេញពី Table modules
        $module->delete();

        // ៣. សម្អាត Permission ក្នុង Spatie (Optional ប៉ុន្តែគួរធ្វើបើចង់ឱ្យ DB ស្អាត)
        if ($permissionName) {
            // លុប Permission ចោល វានឹងលុបទំនាក់ទំនងក្នុង model_has_permissions ដោយស្វ័យប្រវត្តិតាមរយៈ Cascade Delete
            Permission::where('name', $permissionName)->where('guard_name', 'api')->delete();
            // ជម្រះ Cache របស់ Spatie ដើម្បីឱ្យការផ្លាស់ប្តូរមានប្រសិទ្ធភាពភ្លាមៗ
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }

        return response()->json([
            'success' => true,
            'message' => 'លុបម៉ូឌុល និងសិទ្ធិដែលពាក់ព័ន្ធជោគជ័យ'
        ]);
    }
}