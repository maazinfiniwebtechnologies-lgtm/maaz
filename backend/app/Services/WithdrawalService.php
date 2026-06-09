<?php

namespace App\Services;

use App\Models\Withdrawal;
use App\Repositories\WithdrawalRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class WithdrawalService
{
    protected WithdrawalRepository $withdrawalRepository;
    protected WalletRepository $walletRepository;

    public function __construct(
        WithdrawalRepository $withdrawalRepository,
        WalletRepository $walletRepository
    ) {
        $this->withdrawalRepository = $withdrawalRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * Create withdrawal request
     */
    public function createWithdrawal(User $user, array $data): ?Withdrawal
    {
        return DB::transaction(function () use ($user, $data) {
            $wallet = $this->walletRepository->findByUserId($user->id);

            // Check if wallet has sufficient balance
            if (!$wallet || $wallet->balance < $data['amount']) {
                return null;
            }

            $fee = $this->calculateWithdrawalFee($data['amount']);
            $netAmount = $data['amount'] - $fee;

            $withdrawal = $this->withdrawalRepository->create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'fee' => $fee,
                'net_amount' => $netAmount,
                'bank_name' => $data['bank_name'],
                'account_holder' => $data['account_holder'],
                'account_number' => $data['account_number'],
                'account_type' => $data['account_type'] ?? 'checking',
                'status' => 'pending',
            ]);

            // Deduct from wallet
            $this->walletRepository->subtractBalance($wallet, $data['amount']);

            return $withdrawal;
        });
    }

    /**
     * Approve withdrawal
     */
    public function approveWithdrawal(Withdrawal $withdrawal, User $approver): Withdrawal
    {
        return $this->withdrawalRepository->update($withdrawal, [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
        ]);
    }

    /**
     * Reject withdrawal
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, string $reason): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $reason) {
            $withdrawal = $this->withdrawalRepository->update($withdrawal, [
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            // Refund to wallet
            $wallet = $this->walletRepository->findByUserId($withdrawal->user_id);
            $this->walletRepository->addBalance($wallet, $withdrawal->amount);

            return $withdrawal;
        });
    }

    /**
     * Mark withdrawal as processed
     */
    public function markAsProcessed(Withdrawal $withdrawal, string $transactionId): Withdrawal
    {
        return $this->withdrawalRepository->update($withdrawal, [
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'processed_at' => now(),
        ]);
    }

    /**
     * Calculate withdrawal fee
     */
    protected function calculateWithdrawalFee(float $amount): float
    {
        // 2% fee or minimum 50, maximum 500
        $fee = $amount * 0.02;
        return min(max($fee, 50), 500);
    }
}
