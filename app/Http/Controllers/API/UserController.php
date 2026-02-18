<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\{Hash, Storage, DB, Log, Schema};
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    /**
     * 1. Dynamic Middleware Configuration
     * The "Standard" way to protect routes in Laravel 12.
     */
    public static function middleware(): array
    {
        return [
            // Match the database exactly (user_list)
            new Middleware('permission:user_list|user_create|user_edit|user_delete', only: ['index', 'show']),
            new Middleware('permission:user_create', only: ['store']),
            new Middleware('permission:user_edit', only: ['update']),
            new Middleware('role:admin', only: ['destroy']), 
        ];
    }

    /**
     * 2. Index with Dynamic Search
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $searchableColumns = ['name', 'email', 'user_id', 'status'];

            $query->where(function($q) use ($searchableColumns, $search) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        return UserResource::collection($query->latest()->paginate($request->input('per_page', 10)));
    }

    /**
     * 3. Store with Transaction & Logging
     */
    public function store(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($request->password);

            // Handle Avatar
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $data['avatar'] = $file->storeAs('avatars', $filename, 'public');
            }

            $user = User::create($data);

            // Sync Spatie Role
            if ($request->filled('role')) {
                $roleName = is_array($request->role) ? $request->role[0] : $request->role;
                $user->assignRole($roleName);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'បង្កើតជោគជ័យ', 'data' => new UserResource($user)], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User Store Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Error'], 500);
        }
    }

    /**
     * 4. Show (Single Resource)
     */
    public function show(User $user)
    {
        return (new UserResource($user))->additional(['status' => 'success']);
    }

    /**
     * 5. Update with File Cleanup
     */
    public function update(UserRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            if ($request->hasFile('avatar')) {
                // Delete old file
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $file = $request->file('avatar');
                $data['avatar'] = $file->storeAs('avatars', time().'_'.$file->getClientOriginalName(), 'public');
            }

            $user->update($data);

            // Sync Spatie Role
            if ($request->has('role')) {
                $roleName = is_array($request->role) ? $request->role[0] : $request->role;
                $user->syncRoles($roleName);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'ធ្វើបច្ចុប្បន្នភាពជោគជ័យ', 'data' => new UserResource($user->fresh())]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User Update Error: " . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * 6. Profile (Current User)
     */
    public function profile()
    {
        return (new UserResource(auth()->user()))->additional(['status' => 'success']);
    }

    /**
     * 7. Destroy (Secure Delete)
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'មិនអាចលុបខ្លួនឯងបានទេ'], 403);
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
        Log::warning("User Deleted", ['deleted_id' => $user->id, 'by' => auth()->id()]);

        return response()->json(['status' => 'success', 'message' => 'លុបបានជោគជ័យ']);
    }
}