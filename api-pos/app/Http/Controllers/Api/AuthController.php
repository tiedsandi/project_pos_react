<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login User",
     *     tags={"Auth"},
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@email.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="jwt_token_here")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validation->errors(),
            ], 422);
        }

        $crendential = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($crendential)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        return new AuthResource(
            true,
            'Login successful',
            [
                'token' => $token,
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout User",
     *     tags={"Auth"},
     *     operationId="logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout Success")
     *         )
     *     )
     * )
     */

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logout Success']);
    }


    /**
     * @OA\Get(
     *     path="/me",
     *     summary="Get Profile",
     *     tags={"Auth"},
     *     operationId="me",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Fetch profile berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Fetch profile user success."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */

    public function me()
    {
        try {
            $user = auth('api')->user();
            auth('api')->user()->userRole;

            return response()->json([
                'message' => 'Fetch profile user success.',
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/refresh",
     *     summary="Refresh JWT Token",
     *     tags={"Auth"},
     *     operationId="refreshToken",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully."),
     *             @OA\Property(property="token", type="string", example="new_jwt_token")
     *         )
     *     )
     * )
     */

    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return response()->json([
                'message' => 'Token refreshed successfully.',
                'token' => $newToken,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
