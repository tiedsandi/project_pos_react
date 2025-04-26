<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     required={"id", "category_id", "name", "price", "stock"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *     @OA\Property(property="photo", type="string", example="photo.jpg", nullable=true),
 *     @OA\Property(property="description", type="string", example="The latest Apple smartphone", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=1999.99),
 *     @OA\Property(property="stock", type="integer", example=100),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T13:00:00Z")
 * )
 */

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get all products",
     *     tags={"Product"},
     *     operationId="getProducts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products fetched successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = Product::all();

        return new ApiResponseResource(
            $products,
            true,
            'Products fetched successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new product",
     *     tags={"Product"},
     *     operationId="createProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "name", "price", "stock"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *             @OA\Property(property="photo", type="string", example="photo.jpg"),
     *             @OA\Property(property="description", type="string", example="Latest Apple smartphone."),
     *             @OA\Property(property="price", type="number", format="float", example=1999.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
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
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'photo' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
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
            $product = Product::create($request->all());

            return new ApiResponseResource(
                $product,
                true,
                'Product created successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to create product: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Product"},
     *     operationId="getProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);

            return new ApiResponseResource(
                $product,
                true,
                'Product retrieved successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Product not found or an error occurred: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     summary="Update a product",
     *     tags={"Product"},
     *     operationId="updateProduct",
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
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="photo", type="string", example="updated_photo.jpg"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="price", type="number", format="float", example=1499.99),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
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
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'photo' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
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
            $product = Product::findOrFail($id);

            $product->update($request->all());

            return new ApiResponseResource(
                $product,
                true,
                'Product updated successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to update product: ' . $e->getMessage()
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     summary="Delete a product",
     *     tags={"Product"},
     *     operationId="deleteProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return new ApiResponseResource(
                null,
                true,
                'Product deleted successfully'
            );
        } catch (\Exception $e) {
            return new ApiResponseResource(
                null,
                false,
                'Failed to delete product: ' . $e->getMessage()
            );
        }
    }
}
