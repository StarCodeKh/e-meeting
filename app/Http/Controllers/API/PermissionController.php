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
        // ១. កែសម្រួលការ Validate ឱ្យឆែកទាំង name និង guard_name ព្រោះ Spatie អនុញ្ញាតឱ្យឈ្មោះជាន់គ្នា បើ Guard ខុសគ្នា
        $request->validate([
            'name' => [
                'required',
                'string',
                // ឆែក unique តែក្នុង guard 'api' ប៉ុណ្ណោះ
                Rule::unique('permissions')->where(function ($query) {
                    return $query->where('guard_name', 'api');
                }),
            ],
        ]);

        // ២. សម្អាតឈ្មោះ (Slugify) ឱ្យកាន់តែហ្មត់ចត់
        // បន្ថែមការលុប Special characters ដើម្បីការពារ Error ក្នុង Database
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
        $permission = Permission::findOrFail($id);
        $oldName = $permission->name;

        $request->validate([
            'name' => ['required','string',Rule::unique('permissions')->ignore($id)->where('guard_name', 'api')
            ],
        ]);

        // ១. សម្អាតឈ្មោះថ្មី (Format: slug_name)
        $newName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $request->name)));

        // ២. Update ក្នុង Table permissions (Spatie)
        $permission->update([
            'name' => $newName
        ]);

        // ៣. ✅ សំខាន់បំផុត: Update ក្នុង Table modules ដើម្បីកុំឱ្យបាត់ Menu
        // ប្រសិនបើមាន Module ណាដែលប្រើ Permission ឈ្មោះចាស់នេះ ត្រូវដូរវាទៅឈ្មោះថ្មីដែរ
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
        $permission = Permission::findOrFail($id);
        $permissionName = $permission->name;

        // ១. Update Table modules: ប្រសិនបើមាន Module ណាប្រើ Permission នេះ ត្រូវកំណត់វាទៅជា null
        // ដើម្បីកុំឱ្យមាន "ឈ្មោះសិទ្ធិខ្មោច" នៅក្នុង Table modules
        Module::where('permission_name', $permissionName)->update(['permission_name' => null]);

        // ២. លុប Permission ចេញពី Spatie (វានឹងលុបទំនាក់ទំនងជាមួយ Role/User ដោយស្វ័យប្រវត្តិ)
        $permission->delete();

        // ៣. ជម្រះ Cache របស់ Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'status' => 'success',
            'message' => 'លុបសិទ្ធិ និងសម្អាតទិន្នន័យម៉ូឌុលជោគជ័យ'
        ]);
    }
}