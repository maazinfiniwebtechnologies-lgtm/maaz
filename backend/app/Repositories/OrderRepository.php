<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order;
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['items', 'user'])->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->first();
    }

    public function getUserOrders(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('user_id', $userId)->with('items')->paginate($perPage);
    }

    public function getPendingOrders(int $perPage = 15): LengthAwarePaginator
    {
        return Order::pending()->paginate($perPage);
    }

    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Order::byStatus($status)->paginate($perPage);
    }

    public function getByPaymentStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Order::byPaymentStatus($status)->paginate($perPage);
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
