<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    /**
     * Đăng nhập client
     */
    public function login(Request $request)
    {
        try {
            \Log::info('🔐 Login attempt', ['email' => $request->email]);
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                \Log::warning('❌ Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
        } catch (\Exception $e) {
            \Log::error('❌ Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }

        try {
            // Tìm client theo email
            $client = Client::where('email', $request->email)->first();
            \Log::info('🔍 Client lookup', ['found' => !!$client]);

            if (!$client) {
                \Log::warning('❌ Client not found', ['email' => $request->email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tài khoản với email này'
                ], 401);
            }

            // Debug password information
            \Log::info('🔐 Password debug', [
                'email' => $client->email,
                'input_password' => $request->password,
                'stored_password_length' => strlen($client->password),
                'stored_password_prefix' => substr($client->password, 0, 20),
                'password_starts_with_hash' => str_starts_with($client->password, '$2y$'),
                'needs_rehash' => Hash::needsRehash($client->password)
            ]);

            // Kiểm tra password (hỗ trợ cả hash và plaintext để test)
            $passwordValid = false;
            
            if (Hash::needsRehash($client->password)) {
                // Password có thể là plaintext, kiểm tra trực tiếp
                $passwordValid = ($request->password === $client->password);
                \Log::info('🔐 Password check (plaintext)', ['valid' => $passwordValid]);
            } else {
                // Password đã hash, kiểm tra bằng Hash::check
                $passwordValid = Hash::check($request->password, $client->password);
                \Log::info('🔐 Password check (hash)', ['valid' => $passwordValid]);
            }
            
            if (!$passwordValid) {
                \Log::warning('❌ Invalid password', ['email' => $request->email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu không chính xác'
                ], 401);
            }

            // Tạm thời không dùng Sanctum token
            $token = 'simple-token-' . base64_encode($client->email . ':' . time());
            \Log::info('✅ Login successful', ['email' => $client->email]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'user' => $client, // Frontend expect 'user'
                'token' => $token
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Login process error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi trong quá trình đăng nhập: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông tin client hiện tại
     */
    public function user(Request $request)
    {
        $client = $request->user();

        return response()->json([
            'success' => true,
            'user' => $client
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }
}