<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;
use App\Repositories\WalletRepository;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected WalletRepository $walletRepository;
    protected WalletService $walletService;

    public function __construct(
        WalletRepository $walletRepository,
        WalletService $walletService
    ) {
        $this->walletRepository = $walletRepository;
        $this->walletService = $walletService;
    }

    /**
     * Get wallet details
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId($request->user()->id);

        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found',
            ], 404);
        }

        return response()->json([
            'data' => $wallet,
        ]);
    }

    /**
     * Get wallet balance
     */
    public function balance(Request $request): JsonResponse
    {
        $balance = $this->walletService->getBalance($request->user());

        return response()->json([
            'balance' => $balance,
        ]);
    }

    /**
     * Get wallet transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId($request->user()->id);

        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found',
            ], 404);
        }

        $perPage = $request->input('per_page', 15);
        $transactions = $wallet->transactions()->paginate($perPage);

        return response()->json([
            'data' => $transactions->items(),
            'pagination' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
            ],
        ]);
    }

    /**
     * Freeze wallet (Admin)
     */
    public function freeze(Request $request): JsonResponse
    {
        $wallet = $this->walletService->freezeWallet($request->user());

        return response()->json([
            'message' => 'Wallet frozen',
            'data' => $wallet,
        ]);
    }

    /**
     * Unfreeze wallet (Admin)
     */
    public function unfreeze(Request $request): JsonResponse
    {
        $wallet = $this->walletService->unfreezeWallet($request->user());

        return response()->json([
            'message' => 'Wallet unfrozen',
            'data' => $wallet,
        ]);
    }
}
