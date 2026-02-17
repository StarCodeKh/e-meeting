<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = auth()->login($user);

        return response()->json(['user'=> $user,'access_token'=> $token], 201);
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
}
