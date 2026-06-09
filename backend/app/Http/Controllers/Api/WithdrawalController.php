<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreWithdrawalRequest;
use App\Models\Withdrawal;
use App\Repositories\WithdrawalRepository;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    protected WithdrawalRepository $withdrawalRepository;
    protected WithdrawalService $withdrawalService;

    public function __construct(
        WithdrawalRepository $withdrawalRepository,
        WithdrawalService $withdrawalService
    ) {
        $this->withdrawalRepository = $withdrawalRepository;
        $this->withdrawalService = $withdrawalService;
    }

    /**
     * Get all withdrawals (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');

        $query = Withdrawal::query();

        if ($status) {
            $query->where('status', $status);
        }

        $withdrawals = $query->with(['user', 'approver'])->paginate($perPage);

        return response()->json([
            'data' => $withdrawals->items(),
            'pagination' => [
                'total' => $withdrawals->total(),
                'per_page' => $withdrawals->perPage(),
                'current_page' => $withdrawals->currentPage(),
            ],
        ]);
    }

    /**
     * Get user's withdrawals
     */
    public function userWithdrawals(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $withdrawals = $this->withdrawalRepository->getUserWithdrawals($request->user()->id, $perPage);

        return response()->json([
            'data' => $withdrawals->items(),
            'pagination' => [
                'total' => $withdrawals->total(),
                'per_page' => $withdrawals->perPage(),
                'current_page' => $withdrawals->currentPage(),
            ],
        ]);
    }

    /**
     * Get withdrawal details
     */
    public function show(Withdrawal $withdrawal): JsonResponse
    {
        if (auth()->user()->id !== $withdrawal->user_id && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $withdrawal->load(['user', 'approver']),
        ]);
    }

    /**
     * Create withdrawal request
     */
    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $withdrawal = $this->withdrawalService->createWithdrawal($request->user(), $validated);

            if (!$withdrawal) {
                return response()->json([
                    'message' => 'Insufficient balance',
                ], 400);
            }

            return response()->json([
                'message' => 'Withdrawal request created',
                'data' => $withdrawal,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create withdrawal',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Approve withdrawal (Admin)
     */
    public function approve(Withdrawal $withdrawal): JsonResponse
    {
        $withdrawal = $this->withdrawalService->approveWithdrawal($withdrawal, auth()->user());

        return response()->json([
            'message' => 'Withdrawal approved',
            'data' => $withdrawal,
        ]);
    }

    /**
     * Reject withdrawal (Admin)
     */
    public function reject(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $withdrawal = $this->withdrawalService->rejectWithdrawal($withdrawal, $validated['reason']);

        return response()->json([
            'message' => 'Withdrawal rejected',
            'data' => $withdrawal,
        ]);
    }

    /**
     * Mark withdrawal as processed (Admin)
     */
    public function markAsProcessed(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $withdrawal = $this->withdrawalService->markAsProcessed(
            $withdrawal,
            $validated['transaction_id']
        );

        return response()->json([
            'message' => 'Withdrawal marked as processed',
            'data' => $withdrawal,
        ]);
    }
}
