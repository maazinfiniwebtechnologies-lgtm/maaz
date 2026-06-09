<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionSetting;
use App\Models\Order;
use App\Models\User;
use App\Repositories\CommissionRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    protected CommissionRepository $commissionRepository;
    protected WalletRepository $walletRepository;

    public function __construct(
        CommissionRepository $commissionRepository,
        WalletRepository $walletRepository
    ) {
        $this->commissionRepository = $commissionRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * Generate commissions for an order
     */
    public function generateCommissionsForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $user = $order->user;
            $totalPV = $order->total_pv;

            // Generate referral commission
            if ($user->sponsor_id) {
                $this->createReferralCommission($user, $order, $totalPV);
            }

            // Generate level commissions
            $this->generateLevelCommissions($user, $order, $totalPV);

            // Generate rank bonus
            if ($user->rank_id) {
                $this->createRankBonus($user, $order);
            }
        });
    }

    /**
     * Create referral commission
     */
    protected function createReferralCommission(User $user, Order $order, float $pv): void
    {
        $sponsor = $user->sponsor;
        if (!$sponsor) {
            return;
        }

        $setting = CommissionSetting::where('commission_type', 'referral')
            ->where('level', 1)
            ->first();

        if (!$setting) {
            return;
        }

        $amount = ($order->total_amount * $setting->percentage) / 100;

        $this->commissionRepository->create([
            'user_id' => $sponsor->id,
            'source_user_id' => $user->id,
            'order_id' => $order->id,
            'commission_type' => 'referral',
            'level' => 1,
            'amount' => $amount,
            'pv' => $pv,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Credit to wallet
        $wallet = $this->walletRepository->findByUserId($sponsor->id);
        if ($wallet) {
            $this->walletRepository->addBalance($wallet, $amount);
        }
    }

    /**
     * Generate level commissions
     */
    protected function generateLevelCommissions(User $user, Order $order, float $pv): void
    {
        $currentUser = $user;
        $level = 1;
        $maxLevels = 10;

        while ($level <= $maxLevels && $currentUser->sponsor_id) {
            $sponsor = $currentUser->sponsor;
            if (!$sponsor) {
                break;
            }

            $setting = CommissionSetting::where('commission_type', 'level')
                ->where('level', $level)
                ->first();

            if (!$setting || !$setting->is_active) {
                $currentUser = $sponsor;
                $level++;
                continue;
            }

            $amount = ($order->total_amount * $setting->percentage) / 100;

            $this->commissionRepository->create([
                'user_id' => $sponsor->id,
                'source_user_id' => $user->id,
                'order_id' => $order->id,
                'commission_type' => 'level',
                'level' => $level,
                'amount' => $amount,
                'pv' => $pv,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Credit to wallet
            $wallet = $this->walletRepository->findByUserId($sponsor->id);
            if ($wallet) {
                $this->walletRepository->addBalance($wallet, $amount);
            }

            $currentUser = $sponsor;
            $level++;
        }
    }

    /**
     * Create rank bonus
     */
    protected function createRankBonus(User $user, Order $order): void
    {
        if (!$user->rank || $user->rank->bonus <= 0) {
            return;
        }

        $amount = $user->rank->bonus;

        $this->commissionRepository->create([
            'user_id' => $user->id,
            'source_user_id' => $user->id,
            'order_id' => $order->id,
            'commission_type' => 'rank_bonus',
            'level' => 0,
            'amount' => $amount,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Credit to wallet
        $wallet = $this->walletRepository->findByUserId($user->id);
        if ($wallet) {
            $this->walletRepository->addBalance($wallet, $amount);
        }
    }

    /**
     * Approve pending commission
     */
    public function approveCommission(Commission $commission): Commission
    {
        return $this->commissionRepository->update($commission, [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject commission
     */
    public function rejectCommission(Commission $commission): Commission
    {
        return $this->commissionRepository->update($commission, [
            'status' => 'rejected',
        ]);
    }

    /**
     * Get user total commissions
     */
    public function getUserTotalCommissions(User $user): float
    {
        return Commission::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');
    }
}
