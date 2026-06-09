<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $category = $request->input('category');
        $featured = $request->boolean('featured');
        $inStock = $request->boolean('in_stock');

        $query = Product::query();

        if ($category) {
            $query->where('category', $category);
        }

        if ($featured) {
            $query->where('is_featured', true);
        }

        if ($inStock) {
            $query->where('stock', '>', 0);
        }

        $query->where('is_active', true);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
            ],
        ]);
    }

    /**
     * Get featured products
     */
    public function featured(): JsonResponse
    {
        $products = $this->productRepository->getFeatured(10);

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Get product by ID
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Create product (Admin)
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $product = $this->productRepository->create($validated);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update product (Admin)
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $validated = $request->validated();
            $updatedProduct = $this->productRepository->update($product, $validated);

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $updatedProduct,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete product (Admin)
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productRepository->delete($product);

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
