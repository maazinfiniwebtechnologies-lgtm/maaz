<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderRepository $orderRepository;
    protected OrderService $orderService;

    public function __construct(
        OrderRepository $orderRepository,
        OrderService $orderService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * Get all orders (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');
        $paymentStatus = $request->input('payment_status');

        $query = Order::query();

        if ($status) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->with('user')->paginate($perPage);

        return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
            ],
        ]);
    }

    /**
     * Get user's orders
     */
    public function userOrders(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $orders = $this->orderRepository->getUserOrders($request->user()->id, $perPage);

        return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
            ],
        ]);
    }

    /**
     * Get order details
     */
    public function show(Order $order): JsonResponse
    {
        // Check authorization
        if (auth()->user()->id !== $order->user_id && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $order->load('items', 'user'),
        ]);
    }

    /**
     * Create order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $order = $this->orderService->createOrder(
                $request->user(),
                $validated['items'],
                $validated['shipping'] ?? []
            );

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order->load('items'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark order as paid (Admin)
     */
    public function markAsPaid(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $order = $this->orderService->markOrderAsPaid(
            $order,
            $validated['payment_method'],
            $validated['transaction_id'] ?? null
        );

        return response()->json([
            'message' => 'Order marked as paid',
            'data' => $order,
        ]);
    }

    /**
     * Mark order as shipped (Admin)
     */
    public function markAsShipped(Order $order): JsonResponse
    {
        $order = $this->orderService->markOrderAsShipped($order);

        return response()->json([
            'message' => 'Order marked as shipped',
            'data' => $order,
        ]);
    }

    /**
     * Mark order as delivered (Admin)
     */
    public function markAsDelivered(Order $order): JsonResponse
    {
        $order = $this->orderService->markOrderAsDelivered($order);

        return response()->json([
            'message' => 'Order marked as delivered',
            'data' => $order,
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order): JsonResponse
    {
        if (auth()->user()->id !== $order->user_id && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order = $this->orderService->cancelOrder($order);

        return response()->json([
            'message' => 'Order cancelled',
            'data' => $order,
        ]);
    }
}
