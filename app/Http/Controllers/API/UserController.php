<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // ទាញយកបញ្ជីឈ្មោះ User (Index)
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            
            $tableName = (new User())->getTable();
            $columns = Schema::getColumnListing($tableName);

            $query->where(function($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        return UserResource::collection($query->latest()->paginate($request->per_page ?? 10));
    }

    // បង្កើត User ថ្មី
    public function store(UserRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            
            $data['password'] = Hash::make($request->password);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                $nameWithoutExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $nameWithoutExt . '_' . now()->format('Y-m-d_H-i-s') . '.' . $extension;

                $path = $file->storeAs('avatars', $filename, 'public');
                $data['avatar'] = $path;
            }

            $user = User::create($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'បង្កើតអ្នកប្រើប្រាស់ថ្មីបានជោគជ័យ!',
                'data'    => new UserResource($user)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User Store Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចបង្កើតអ្នកប្រើប្រាស់បានទេ!',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // បង្ហាញព័ត៌មាន User ម្នាក់
    public function show(User $user)
    {
        try {
            if (auth()->user()->role !== 'admin' && $user->id !== auth()->id()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'អ្នកមិនមានសិទ្ធិចូលមើលព័ត៌មានអ្នកប្រើប្រាស់ផ្សេងឡើយ'
                ], 403);
            }

            return (new UserResource($user))
                ->additional([
                    'status' => 'success'
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'រកមិនឃើញអ្នកប្រើប្រាស់នេះឡើយ',
            ], 404);
        }
    }

    // កែប្រែព័ត៌មាន User
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
                if ($user->avatar && Storage::disk('public')->exists(str_replace('storage/', '', $user->avatar))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $user->avatar));
                }

                $file = $request->file('avatar');
                $nameWithoutExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $nameWithoutExt . '_' . now()->format('Y-m-d_H-i-s') . '.' . $extension;

                $path = $file->storeAs('avatars', $filename, 'public');
                $data['avatar'] = $path;
            }

            $user->update($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'កែប្រែព័ត៌មានអ្នកប្រើប្រាស់ជោគជ័យ!',
                'data'    => new UserResource($user->fresh())
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User Update Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចកែប្រែបានទេ!',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // ទាញយកព័ត៌មាន Profile ផ្ទាល់ខ្លួន (CurrentUser)
    public function profile()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'សូមចូលប្រព័ន្ធជាមុនសិន'
                ], 401);
            }

            return (new UserResource($user))
                ->additional([
                    'status' => 'success',
                    'message' => 'ទាញយកទិន្នន័យ Profile ជោគជ័យ'
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // លុប User (Soft Delete)
    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'អ្នកមិនអាចលុបគណនីដែលកំពុងប្រើប្រាស់បានទេ'
                ], 403);
            }

            if ($user->avatar) {
                $filePath = str_replace('storage/', '', $user->avatar);
                
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            $user->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'លុបអ្នកប្រើប្រាស់បានជោគជ័យ'
            ], 200);

        } catch (\Exception $e) {
            Log::error("User Delete Error: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចលុបអ្នកប្រើប្រាស់បានទេ',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}