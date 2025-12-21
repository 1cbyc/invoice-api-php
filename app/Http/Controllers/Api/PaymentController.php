<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Record a payment for an invoice.
     */
    public function store(Request $request, int $invoiceId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $payment = $this->paymentService->recordPayment($invoiceId, $request->all());
            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while recording the payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all payments for an invoice.
     */
    public function index(int $invoiceId): JsonResponse
    {
        $payments = $this->paymentService->getInvoicePayments($invoiceId);
        return response()->json($payments);
    }
}
