<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"User"},
     *     operationId="getUsers",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users fetched successfully"),
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $users = User::with('userRole.role')->get();

        return UserResource::collection($users);
    }

    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Create a new user",
     *     tags={"User"},
     *     operationId="createUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="roles", type="array", 
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'array|exists:roles,id',
        ]);

        if ($validation->fails()) {
            return new ApiResponseResource(
                ['errors' => $validation->errors()],
                false,
                'Validation failed'
            );
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            if (isset($request->roles)) {
                $user->roles()->sync($request->roles);
            }

            return new ApiResponseResource(
                new UserResource($user),
                true,
                'User created successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to create user: ' . $e->getMessage()
            );
        }
    }
    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get a specific user",
     *     tags={"User"},
     *     operationId="getUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $users = User::with('userRole.role')->findOrFail($id);
            return new ApiResponseResource(
                new UserResource($users),
                true,
                'User retrieved successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'User not found or an error occurred: ' . $e->getMessage()
            );
        }
    }
    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update a user",
     *     tags={"User"},
     *     operationId="updateUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", example="jane.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="roles", type="array", 
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'roles' => 'nullable|array|exists:roles,id',
        ]);

        if ($validated->fails()) {
            return new ApiResponseResource(
                ['errors' => $validated->errors()],
                false,
                'Validation failed'
            );
        }

        try {
            $user = User::findOrFail($id);

            $user->name = $request->input('name', $user->name);
            if ($request->has('email') && $request->input('email') !== $user->email) {
                $user->email = $request->input('email');
            }

            if ($request->filled('password')) {
                $user->password = bcrypt($request->input('password'));
            }

            if ($request->has('roles')) {
                $user->roles()->sync($request->input('roles'));
            }

            $user->save();

            return new ApiResponseResource(
                new UserResource($user),
                true,
                'User updated successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to update user: ' . $e->getMessage()
            );
        }
    }
    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     tags={"User"},
     *     operationId="deleteUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return new ApiResponseResource(
                null,
                true,
                'User deleted successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to delete user: ' . $e->getMessage()
            );
        }
    }
}
