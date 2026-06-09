<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Product::active()->paginate($perPage);
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return Product::featured()->limit($limit)->get();
    }

    public function getByCategory(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return Product::byCategory($category)->paginate($perPage);
    }

    public function getInStock(int $perPage = 15): LengthAwarePaginator
    {
        return Product::inStock()->paginate($perPage);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
