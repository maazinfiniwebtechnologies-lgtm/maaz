<?php

namespace App\Repositories;

use App\Models\Commission;
use Illuminate\Pagination\LengthAwarePaginator;

class CommissionRepository
{
    public function create(array $data): Commission
    {
        return Commission::create($data);
    }

    public function update(Commission $commission, array $data): Commission
    {
        $commission->update($data);
        return $commission;
    }

    public function findById(int $id): ?Commission
    {
        return Commission::find($id);
    }

    public function getUserCommissions(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Commission::where('user_id', $userId)->with(['sourceUser', 'order'])->paginate($perPage);
    }

    public function getPendingCommissions(int $perPage = 15): LengthAwarePaginator
    {
        return Commission::pending()->paginate($perPage);
    }

    public function getApprovedCommissions(int $userId): array
    {
        return Commission::where('user_id', $userId)->approved()->get()->toArray();
    }

    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return Commission::byType($type)->paginate($perPage);
    }

    public function delete(Commission $commission): bool
    {
        return $commission->delete();
    }
}
