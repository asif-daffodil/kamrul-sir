<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\SslCommerzCancelRequest;
use App\Http\Requests\Payment\SslCommerzFailureRequest;
use App\Http\Requests\Payment\SslCommerzIpnRequest;
use App\Http\Requests\Payment\SslCommerzSuccessRequest;
use App\Services\Payment\SslCommerzService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SslCommerzCallbackController extends Controller
{
    public function __construct(
        protected SslCommerzService $sslCommerzService
    ) {}

    /**
     * Handle SSLCommerz Success callback.
     * Note: Never trusts redirect POST parameters directly; strictly verifies
     * transaction against SSLCommerz validation API before marking order paid.
     */
    public function success(SslCommerzSuccessRequest $request): RedirectResponse
    {
        $result = $this->sslCommerzService->processSuccessPayment(
            tranId: (string) $request->input('tran_id'),
            valId: (string) $request->input('val_id'),
            rawPayload: $request->all()
        );

        if (in_array($result['status'], ['success', 'already_processed'])) {
            $orderNumber = $result['order']->order_number;

            return redirect()
                ->route('checkout.success', $orderNumber)
                ->with('status', 'Your online payment was successfully processed!');
        }

        return redirect()
            ->route('checkout.index')
            ->with('error', 'Online payment verification failed: ' . ($result['message'] ?? 'Please contact customer support.'));
    }

    /**
     * Handle SSLCommerz Failure callback.
     */
    public function fail(SslCommerzFailureRequest $request): RedirectResponse
    {
        $result = $this->sslCommerzService->handleFailure(
            tranId: (string) $request->input('tran_id'),
            rawPayload: $request->all()
        );

        return redirect()
            ->route('checkout.index')
            ->with('error', 'Online payment was declined or failed. You may retry or select Cash on Delivery.');
    }

    /**
     * Handle SSLCommerz Cancel callback.
     */
    public function cancel(SslCommerzCancelRequest $request): RedirectResponse
    {
        $result = $this->sslCommerzService->handleCancel(
            tranId: (string) $request->input('tran_id'),
            rawPayload: $request->all()
        );

        return redirect()
            ->route('checkout.index')
            ->with('warning', 'Online payment was cancelled. You may retry or select Cash on Delivery.');
    }

    /**
     * Handle SSLCommerz Server-to-Server Instant Payment Notification (IPN).
     * Idempotent endpoint: Duplicate notifications are acknowledged safely
     * without redundant processing or state mutation.
     */
    public function ipn(SslCommerzIpnRequest $request): JsonResponse
    {
        $result = $this->sslCommerzService->handleIpn($request->all());

        $statusCode = in_array($result['status'], ['validation_failed', 'amount_mismatch', 'mismatch'])
            ? 400
            : 200;

        return response()->json($result, $statusCode);
    }
}
