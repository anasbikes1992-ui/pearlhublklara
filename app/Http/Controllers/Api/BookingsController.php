<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CommissionService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingsController extends Controller
{
    public function __construct(
        protected CommissionService $commissionService,
        protected PaymentService $paymentService,
    ) {}
    public function index(Request $request)
    {
        return response()->json(
            Booking::where('user_id', $request->user()->id)
                ->with('provider')
                ->latest()
                ->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'listing_id' => 'required|uuid',
            'listing_type' => 'required|in:stay,vehicle,event,property',
            'provider_id' => 'nullable|uuid',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'guests' => 'nullable|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';
        $data['payment_status'] = 'pending';

        $booking = Booking::create($data);

        return response()->json($booking, 201);
    }

    public function show(Request $request, string $id)
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->with(['provider', 'earning'])
            ->findOrFail($id);

        return response()->json($booking);
    }

    public function cancel(Request $request, string $id)
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($id);

        DB::transaction(function () use ($booking) {
            $newPaymentStatus = $booking->payment_status;

            if ($booking->payment_status === 'completed' && $booking->payment_method) {
                try {
                    $this->paymentService->driver($booking->payment_method)->refund(
                        $booking->id,
                        $booking->total_price
                    );
                    $newPaymentStatus = 'refunded';
                } catch (\Exception $e) {
                    report($e);
                    $newPaymentStatus = 'refund_pending';
                }
            } elseif ($booking->payment_status !== 'completed') {
                $newPaymentStatus = 'failed';
            }

            $booking->update([
                'status' => 'cancelled',
                'payment_status' => $newPaymentStatus,
            ]);
        });

        return response()->json(['message' => 'Booking cancelled.', 'booking' => $booking->fresh()]);
    }

    public function providerBookings(Request $request)
    {
        return response()->json(
            Booking::where('provider_id', $request->user()->id)
                ->with('user')
                ->latest()
                ->paginate(15)
        );
    }

    public function updateStatus(Request $request, string $id)
    {
        $booking = Booking::where('provider_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        DB::transaction(function () use ($booking, $data, $request) {
            $booking->update(['status' => $data['status']]);

            if ($data['status'] === 'completed' && $booking->payment_status === 'completed') {
                $this->commissionService->processBookingCommission(
                    $request->user()->id,
                    $booking->id,
                    $booking->total_price,
                    $booking->listing_type
                );
            }
        });

        return response()->json($booking->fresh());
    }
}
