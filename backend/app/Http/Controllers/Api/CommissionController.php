<?php

namespace App\Http\Controllers\Api;

use App\Models\Commission;
use App\Repositories\CommissionRepository;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected CommissionRepository $commissionRepository;
    protected CommissionService $commissionService;

    public function __construct(
        CommissionRepository $commissionRepository,
        CommissionService $commissionService
    ) {
        $this->commissionRepository = $commissionRepository;
        $this->commissionService = $commissionService;
    }

    /**
     * Get all commissions (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $type = $request->input('type');
        $status = $request->input('status');

        $query = Commission::query();

        if ($type) {
            $query->where('commission_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $commissions = $query->with(['user', 'sourceUser'])->paginate($perPage);

        return response()->json([
            'data' => $commissions->items(),
            'pagination' => [
                'total' => $commissions->total(),
                'per_page' => $commissions->perPage(),
                'current_page' => $commissions->currentPage(),
            ],
        ]);
    }

    /**
     * Get user's commissions
     */
    public function userCommissions(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $commissions = $this->commissionRepository->getUserCommissions($request->user()->id, $perPage);

        return response()->json([
            'data' => $commissions->items(),
            'pagination' => [
                'total' => $commissions->total(),
                'per_page' => $commissions->perPage(),
                'current_page' => $commissions->currentPage(),
            ],
        ]);
    }

    /**
     * Get commission details
     */
    public function show(Commission $commission): JsonResponse
    {
        return response()->json([
            'data' => $commission->load(['user', 'sourceUser', 'order']),
        ]);
    }

    /**
     * Approve commission (Admin)
     */
    public function approve(Commission $commission): JsonResponse
    {
        $commission = $this->commissionService->approveCommission($commission);

        return response()->json([
            'message' => 'Commission approved',
            'data' => $commission,
        ]);
    }

    /**
     * Reject commission (Admin)
     */
    public function reject(Request $request, Commission $commission): JsonResponse
    {
        $commission = $this->commissionService->rejectCommission($commission);

        return response()->json([
            'message' => 'Commission rejected',
            'data' => $commission,
        ]);
    }

    /**
     * Get user's total commissions
     */
    public function userTotal(Request $request): JsonResponse
    {
        $total = $this->commissionService->getUserTotalCommissions($request->user());

        return response()->json([
            'total_commissions' => $total,
        ]);
    }
}
