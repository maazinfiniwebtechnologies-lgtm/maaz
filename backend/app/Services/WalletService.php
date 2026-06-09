<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class WalletService
{
    protected WalletRepository $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * Create wallet for user
     */
    public function createWallet(User $user): Wallet
    {
        return $this->walletRepository->create([
            'user_id' => $user->id,
            'balance' => 0,
            'total_earned' => 0,
            'total_withdrawn' => 0,
        ]);
    }

    /**
     * Credit amount to wallet
     */
    public function creditWallet(User $user, float $amount, string $referenceType = null, int $referenceId = null, string $remarks = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $remarks) {
            $wallet = $this->walletRepository->findByUserId($user->id);

            if (!$wallet) {
                $wallet = $this->createWallet($user);
            }

            $balanceBefore = $wallet->balance;
            $wallet = $this->walletRepository->addBalance($wallet, $amount);
            $balanceAfter = $wallet->balance;

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'remarks' => $remarks,
            ]);
        });
    }

    /**
     * Debit amount from wallet
     */
    public function debitWallet(User $user, float $amount, string $referenceType = null, int $referenceId = null, string $remarks = null): ?WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $remarks) {
            $wallet = $this->walletRepository->findByUserId($user->id);

            if (!$wallet || $wallet->balance < $amount) {
                return null;
            }

            $balanceBefore = $wallet->balance;
            $wallet = $this->walletRepository->subtractBalance($wallet, $amount);
            $balanceAfter = $wallet->balance;

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'remarks' => $remarks,
            ]);
        });
    }

    /**
     * Get wallet balance
     */
    public function getBalance(User $user): float
    {
        $wallet = $this->walletRepository->findByUserId($user->id);
        return $wallet ? $wallet->balance : 0;
    }

    /**
     * Freeze wallet
     */
    public function freezeWallet(User $user): Wallet
    {
        $wallet = $this->walletRepository->findByUserId($user->id);
        return $this->walletRepository->freeze($wallet);
    }

    /**
     * Unfreeze wallet
     */
    public function unfreezeWallet(User $user): Wallet
    {
        $wallet = $this->walletRepository->findByUserId($user->id);
        return $this->walletRepository->unfreeze($wallet);
    }
}
