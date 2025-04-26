<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Get all categories",
     *     tags={"Category"},
     *     operationId="getCategories",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories fetched successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Category")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = Category::all();

        return new ApiResponseResource(
            $categories,
            true,
            'Categories fetched successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     summary="Create a new category",
     *     tags={"Category"},
     *     operationId="createCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="description", type="string", example="Electronic gadgets and accessories"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
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
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validation->fails()) {
            return new ApiResponseResource(
                ['errors' => $validation->errors()],
                false,
                'Validation failed'
            );
        }

        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return new ApiResponseResource(
                $category,
                true,
                'Category created successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to create category: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     summary="Get a specific category",
     *     tags={"Category"},
     *     operationId="getCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);

            return new ApiResponseResource(
                $category,
                true,
                'Category retrieved successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Category not found or an error occurred: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     summary="Update a category",
     *     tags={"Category"},
     *     operationId="updateCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="New Category Name"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validated->fails()) {
            return new ApiResponseResource(
                ['errors' => $validated->errors()],
                false,
                'Validation failed'
            );
        }

        try {
            $category = Category::findOrFail($id);

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? $category->is_active,
            ]);

            return new ApiResponseResource(
                $category,
                true,
                'Category updated successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to update category: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     summary="Delete a category",
     *     tags={"Category"},
     *     operationId="deleteCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return new ApiResponseResource(
                null,
                true,
                'Category deleted successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to delete category: ' . $e->getMessage()
            );
        }
    }
}
