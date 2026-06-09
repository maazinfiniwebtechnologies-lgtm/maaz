<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByReferralCode(string $code): ?User
    {
        return User::where('referral_code', $code)->first();
    }

    public function getAll(int $perPage = 15): mixed
    {
        return User::paginate($perPage);
    }

    public function getActive(int $perPage = 15): mixed
    {
        return User::active()->paginate($perPage);
    }

    public function getDownlines(User $user): Collection
    {
        return $user->downlines()->get();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
