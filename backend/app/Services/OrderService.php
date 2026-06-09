<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class OrderService
{
    protected OrderRepository $orderRepository;
    protected CommissionService $commissionService;

    public function __construct(
        OrderRepository $orderRepository,
        CommissionService $commissionService
    ) {
        $this->orderRepository = $orderRepository;
        $this->commissionService = $commissionService;
    }

    /**
     * Create order from cart items
     */
    public function createOrder(User $user, array $items, array $shipping = []): Order
    {
        return DB::transaction(function () use ($user, $items, $shipping) {
            $subtotal = 0;
            $totalPV = 0;
            $tax = $shipping['tax'] ?? 0;
            $shippingFee = $shipping['fee'] ?? 0;
            $discount = $shipping['discount'] ?? 0;

            // Calculate totals
            foreach ($items as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $subtotal += $itemTotal;
                $totalPV += $item['pv'] * $item['quantity'];
            }

            $totalAmount = $subtotal + $tax + $shippingFee - $discount;

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shippingFee,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'total_pv' => $totalPV,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'shipping_address' => json_encode($shipping['address'] ?? []),
                'billing_address' => json_encode($shipping['billing'] ?? []),
            ]);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'pv' => $item['pv'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            return $order;
        });
    }

    /**
     * Mark order as paid
     */
    public function markOrderAsPaid(Order $order, string $paymentMethod, string $transactionId = null): Order
    {
        $order = $this->orderRepository->update($order, [
            'payment_status' => 'completed',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);

        // Generate commissions
        $this->commissionService->generateCommissionsForOrder($order);

        // Update MLM PV
        if ($order->user->sponsor) {
            app(MLMService::class)->updateUserPV($order->user, $order->total_pv);
        }

        return $order;
    }

    /**
     * Mark order as shipped
     */
    public function markOrderAsShipped(Order $order): Order
    {
        return $this->orderRepository->update($order, [
            'order_status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    /**
     * Mark order as delivered
     */
    public function markOrderAsDelivered(Order $order): Order
    {
        return $this->orderRepository->update($order, [
            'order_status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order): Order
    {
        return $this->orderRepository->update($order, [
            'order_status' => 'cancelled',
        ]);
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD' . date('YmdHis') . Str::random(4);
        } while ($this->orderRepository->findByOrderNumber($number));

        return $number;
    }
}
