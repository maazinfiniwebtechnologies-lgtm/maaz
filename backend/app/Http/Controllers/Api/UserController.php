<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\MLMService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserRepository $userRepository;
    protected MLMService $mlmService;

    public function __construct(
        UserRepository $userRepository,
        MLMService $mlmService
    ) {
        $this->userRepository = $userRepository;
        $this->mlmService = $mlmService;
    }

    /**
     * Get all users
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $users = $this->userRepository->getAll($perPage);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Get user details
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $user->load(['sponsor', 'rank', 'network', 'wallet']),
        ]);
    }

    /**
     * Update user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $updatedUser = $this->userRepository->update($user, $validated);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $updatedUser,
        ]);
    }

    /**
     * Get user team hierarchy
     */
    public function getTeamHierarchy(User $user): JsonResponse
    {
        $hierarchy = $this->mlmService->getUserTeamHierarchy($user);

        return response()->json([
            'data' => $hierarchy,
        ]);
    }

    /**
     * Get user team statistics
     */
    public function getTeamStats(User $user): JsonResponse
    {
        $stats = $this->mlmService->getTeamStats($user);

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get user downlines
     */
    public function getDownlines(User $user, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $downlines = $user->downlines()->paginate($perPage);

        return response()->json([
            'data' => $downlines->items(),
            'pagination' => [
                'total' => $downlines->total(),
                'per_page' => $downlines->perPage(),
                'current_page' => $downlines->currentPage(),
            ],
        ]);
    }
}
