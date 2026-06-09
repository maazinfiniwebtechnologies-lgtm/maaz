<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNetwork;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MLMService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user with MLM hierarchy
     */
    public function registerUser(array $data, ?string $sponsorReferralCode = null): User
    {
        return DB::transaction(function () use ($data, $sponsorReferralCode) {
            // Generate referral code
            $data['referral_code'] = $this->generateReferralCode();

            // Find sponsor
            if ($sponsorReferralCode) {
                $sponsor = $this->userRepository->findByReferralCode($sponsorReferralCode);
                if ($sponsor) {
                    $data['sponsor_id'] = $sponsor->id;
                }
            }

            // Create user
            $user = $this->userRepository->create($data);

            // Create user network entry
            $this->createUserNetwork($user, $data['sponsor_id'] ?? null);

            // Create wallet
            app(WalletService::class)->createWallet($user);

            return $user;
        });
    }

    /**
     * Create user network record
     */
    protected function createUserNetwork(User $user, ?int $sponsorId = null): UserNetwork
    {
        $level = 0;
        $position = null;

        if ($sponsorId) {
            $sponsor = $this->userRepository->findById($sponsorId);
            $sponsorNetwork = $sponsor->network;
            $level = $sponsorNetwork->level + 1;
            $position = $this->determinePosition($sponsor);
        }

        return UserNetwork::create([
            'user_id' => $user->id,
            'parent_id' => $sponsorId,
            'position' => $position,
            'level' => $level,
            'downline_count' => 0,
            'total_pv' => 0,
        ]);
    }

    /**
     * Determine position (left or right) for new user
     */
    protected function determinePosition(User $sponsor): string
    {
        $leftCount = $sponsor->downlines()
            ->whereHas('network', fn($q) => $q->where('position', 'left'))
            ->count();

        $rightCount = $sponsor->downlines()
            ->whereHas('network', fn($q) => $q->where('position', 'right'))
            ->count();

        return $leftCount <= $rightCount ? 'left' : 'right';
    }

    /**
     * Get user team hierarchy
     */
    public function getUserTeamHierarchy(User $user, int $levels = 10): array
    {
        return [
            'user' => $user,
            'downlines' => $this->getDownlineHierarchy($user, $levels),
        ];
    }

    /**
     * Get downline hierarchy recursively
     */
    protected function getDownlineHierarchy(User $user, int $remainingLevels = 10): array
    {
        if ($remainingLevels <= 0) {
            return [];
        }

        return $user->downlines()
            ->get()
            ->map(fn($downline) => [
                'user' => $downline,
                'downlines' => $this->getDownlineHierarchy($downline, $remainingLevels - 1),
            ])
            ->toArray();
    }

    /**
     * Get team statistics
     */
    public function getTeamStats(User $user): array
    {
        $network = $user->network;

        return [
            'total_downlines' => $network->downline_count,
            'left_team_count' => $user->downlines()
                ->whereHas('network', fn($q) => $q->where('position', 'left'))
                ->count(),
            'right_team_count' => $user->downlines()
                ->whereHas('network', fn($q) => $q->where('position', 'right'))
                ->count(),
            'total_pv' => $network->total_pv,
            'team_level' => $network->level,
        ];
    }

    /**
     * Update PV (Point Value)
     */
    public function updateUserPV(User $user, float $pv): void
    {
        $network = $user->network;
        $network->increment('total_pv', $pv);

        // Update sponsor PV as well
        if ($user->sponsor) {
            $this->updateUserPV($user->sponsor, $pv);
        }
    }

    /**
     * Generate unique referral code
     */
    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while ($this->userRepository->findByReferralCode($code));

        return $code;
    }
}
