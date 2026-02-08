<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Module::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_name' => 'required|string|unique:modules,key_name',
            'label' => 'required|string|max:255'
        ]);

        $module = Module::create($validated);

        return response()->json(['data' => $module], 201);
    }

}
