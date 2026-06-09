<?php

namespace App\Repositories;

use App\Models\Withdrawal;
use Illuminate\Pagination\LengthAwarePaginator;

class WithdrawalRepository
{
    public function create(array $data): Withdrawal
    {
        return Withdrawal::create($data);
    }

    public function update(Withdrawal $withdrawal, array $data): Withdrawal
    {
        $withdrawal->update($data);
        return $withdrawal;
    }

    public function findById(int $id): ?Withdrawal
    {
        return Withdrawal::find($id);
    }

    public function getUserWithdrawals(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Withdrawal::where('user_id', $userId)->paginate($perPage);
    }

    public function getPendingWithdrawals(int $perPage = 15): LengthAwarePaginator
    {
        return Withdrawal::pending()->paginate($perPage);
    }

    public function getApprovedWithdrawals(int $perPage = 15): LengthAwarePaginator
    {
        return Withdrawal::approved()->paginate($perPage);
    }

    public function getCompletedWithdrawals(int $perPage = 15): LengthAwarePaginator
    {
        return Withdrawal::completed()->paginate($perPage);
    }

    public function delete(Withdrawal $withdrawal): bool
    {
        return $withdrawal->delete();
    }
}
