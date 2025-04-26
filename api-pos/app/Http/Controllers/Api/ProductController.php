<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $products = Product::get();

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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"category_id", "name", "price", "stock"},
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *                 @OA\Property(property="photo", type="string", format="binary"),
     *                 @OA\Property(property="description", type="string", example="Latest Apple smartphone."),
     *                 @OA\Property(property="price", type="number", format="float", example=1999.99),
     *                 @OA\Property(property="stock", type="integer", example=100),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
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
        $request->merge([
            'is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);

        $validation = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            $imageName = null;
            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                $image->storeAs('products', $image->hashName());
                $imageName = $image->hashName();
            }

            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'photo' =>  $imageName,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'is_active' => $request->is_active ?? true,
            ]);

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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Product Name"),
     *                 @OA\Property(property="photo", type="string", format="binary"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="price", type="number", format="float", example=1499.99),
     *                 @OA\Property(property="stock", type="integer", example=50),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
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
        $request->merge([
            'is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);

        // Validasi
        $validated = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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

            $updateData = [];
            if ($request->filled('category_id')) {
                $updateData['category_id'] = $request->category_id;
            }
            if ($request->filled('name')) {
                $updateData['name'] = $request->name;
            }
            if ($request->filled('description')) {
                $updateData['description'] = $request->description;
            }
            if ($request->filled('price')) {
                $updateData['price'] = $request->price;
            }
            if ($request->filled('stock')) {
                $updateData['stock'] = $request->stock;
            }
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->is_active;
            }

            if ($request->hasFile('photo')) {
                if ($product->photo) {
                    Storage::delete('products/' . $product->photo);
                }

                $image = $request->file('photo');
                $image->storeAs('products', $image->hashName());

                $updateData['photo'] = $image->hashName();
            }

            $product->update($updateData);

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
            Storage::delete('products/' . basename($product->photo));
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
