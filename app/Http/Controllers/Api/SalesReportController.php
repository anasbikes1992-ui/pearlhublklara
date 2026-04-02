<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderSalesReport;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function __construct(protected CommissionService $commissionService) {}

    public function index(Request $request): JsonResponse
    {
        $reports = ProviderSalesReport::where('provider_id', $request->user()->profile->id)
            ->orderBy('month', 'desc')
            ->paginate(12);

        return response()->json($reports);
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_id' => 'required|uuid|exists:sme_businesses,id',
            'month' => 'required|date_format:Y-m',
            'total_sales' => 'required|numeric|min:0',
            'inquiry_count' => 'nullable|integer|min:0',
        ]);

        $report = $this->commissionService->processSmeCommission(
            $request->user()->profile->id,
            $validated['business_id'],
            (float) $validated['total_sales'],
            $validated['month']
        );

        return response()->json(['data' => $report], 201);
    }

    public function show(string $id): JsonResponse
    {
        $report = ProviderSalesReport::findOrFail($id);

        return response()->json(['data' => $report]);
    }
}
