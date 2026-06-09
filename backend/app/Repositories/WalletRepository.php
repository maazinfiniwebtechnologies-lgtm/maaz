<?php

namespace App\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    public function create(array $data): Wallet
    {
        return Wallet::create($data);
    }

    public function findByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    public function update(Wallet $wallet, array $data): Wallet
    {
        $wallet->update($data);
        return $wallet;
    }

    public function addBalance(Wallet $wallet, float $amount): Wallet
    {
        $wallet->increment('balance', $amount);
        $wallet->increment('total_earned', $amount);
        return $wallet->fresh();
    }

    public function subtractBalance(Wallet $wallet, float $amount): Wallet
    {
        $wallet->decrement('balance', $amount);
        return $wallet->fresh();
    }

    public function freeze(Wallet $wallet): Wallet
    {
        $wallet->update(['is_frozen' => true]);
        return $wallet;
    }

    public function unfreeze(Wallet $wallet): Wallet
    {
        $wallet->update(['is_frozen' => false]);
        return $wallet;
    }
}
