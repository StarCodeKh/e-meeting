<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Action;
use Illuminate\Http\JsonResponse;

class ActionController extends Controller
{
    /**
     * List all actions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $actions = Action::orderBy('sort_order', 'asc')->get();

        return response()->json([
            'data' => $actions,
        ]);
    }

    /**
     * Create a new action.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request data
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:actions,name',
            'label' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $action = Action::create([
            'name' => $validated['name'],
            'label' => $validated['label'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'message' => 'Action created successfully.',
            'data' => $action,
        ], 201);
    }
}
