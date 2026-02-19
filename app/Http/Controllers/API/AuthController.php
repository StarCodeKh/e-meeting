<?php
namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    private $privateKey;

    public function __construct()
    {
        // Load private key from storage
        $this->privateKey = file_get_contents(storage_path('rsa/private.pem'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            // ២. Assign Role "USER"
            $role = Role::firstOrCreate([
                'name' => 'USER', 
                'guard_name' => 'api'
            ]);
            
            $user->assignRole($role);

            DB::commit();

            $token = auth()->login($user);

            return response()->json([
                'status'       => 'success',
                'message'      => 'ចុះឈ្មោះជោគជ័យ!',
                'user'         => $user->load('roles'),
                'access_token' => $token,
                'token_type'   => 'bearer',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Registration Error: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'មានបញ្ហាបច្ចេកទេស មិនអាចចុះឈ្មោះបានទេ!',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Decrypt the RSA encrypted password sent from frontend
        $encryptedPassword = base64_decode($request->password);

        $decryptedPassword = null;

        // Use openssl_private_decrypt to decrypt
        if (!openssl_private_decrypt($encryptedPassword, $decryptedPassword, $this->privateKey)) {
            return response()->json(['message' => 'Password decryption failed'], 400);
        }

        // Now attempt to login using decrypted password
        if (!Auth::attempt(['email' => $request->email, 'password' => $decryptedPassword])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = auth()->login($user);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ]);
    }

    public function refresh()
    {
        $token = auth()->refresh();
        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ]);
    }

    public function changePassword(Request $request)
    {
        // ១. Decrypt Password ទាំងចាស់ និងថ្មីសិន
        $oldPassword = $this->decryptRsa($request->old_password);
        $newPassword = $this->decryptRsa($request->password);
        $confirmPassword = $this->decryptRsa($request->password_confirmation);

        if (!$oldPassword || !$newPassword) {
            return response()->json(['message' => 'ការបំប្លែងកូដសម្ងាត់មិនត្រឹមត្រូវ!'], 400);
        }

        // ២. បង្កើត Array ថ្មីដើម្បី Validate ទិន្នន័យដែលដោះកូដរួច
        $validator = Validator::make([
            'old_password' => $oldPassword,
            'password' => $newPassword,
            'password_confirmation' => $confirmPassword,
        ], [
            'old_password' => 'required',
            'password'     => 'required|string|min:8|confirmed', // ប្តូរទៅ 8 តាម Vue
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // ៣. ផ្ទៀងផ្ទាត់ Password ចាស់ជាមួយ Database
        if (!Hash::check($oldPassword, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'លេខសម្ងាត់ចាស់មិនត្រឹមត្រូវទេ!'
            ], 422);
        }

        // ៤. រក្សាទុក Password ថ្មី
        DB::beginTransaction();
        try {
            $user->update([
                'password' => Hash::make($newPassword)
            ]);
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'ផ្លាស់ប្តូរកូដសម្ងាត់ជោគជ័យ!',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'មានបញ្ហាបច្ចេកទេស!'], 500);
        }
    }

    /**
     * Helper function សម្រាប់ Decrypt RSA ឱ្យកូដមើលទៅ Clean
     */
    private function decryptRsa($encryptedBase64)
    {
        $encrypted = base64_decode($encryptedBase64);
        $decrypted = null;
        if (openssl_private_decrypt($encrypted, $decrypted, $this->privateKey)) {
            return $decrypted;
        }
        return null;
    }
}