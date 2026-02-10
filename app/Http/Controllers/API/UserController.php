<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ១. ទាញយកបញ្ជីឈ្មោះ User (Index)
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('custom_id', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            // Sorting
            ->orderBy($request->sort_by ?? 'created_at', $request->order ?? 'desc')
            ->paginate($request->per_page ?? 10);

        return UserResource::collection($users);
    }

    // ២. បង្កើត User ថ្មី
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($request->password);
        
        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully',
            'data'    => new UserResource($user)
        ], 201);
    }

    // ៣. បង្ហាញព័ត៌មាន User ម្នាក់
    public function show(User $user)
    {
        return new UserResource($user);
    }

    // ៤. កែប្រែព័ត៌មាន User
    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'data'    => new UserResource($user)
        ]);
    }

    // ៥. លុប User (Soft Delete)
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}