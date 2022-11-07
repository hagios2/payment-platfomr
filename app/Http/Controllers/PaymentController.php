<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardDetailsRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Interfaces\PaymentGateway;
use App\Services\StripeService;

class PaymentController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function addCardDetails(CardDetailsRequest $request, StripeService $stripeService)
    {
        $user = User::find(1);

        $stripeService->createCustomer($user, [
            'card' => $request->validated()
        ]);

        return response()->json(['message' => 'card details added successfully']);
    }

    public function initStripePayment(PaymentRequest $request, StripeService $stripeService)
    {
        $data = $request->validated();

        $transaction = $this->makePayment($stripeService, $data);
    }

    public function refundStripePayment(PaymentRequest $request, StripeService $stripeService)
    {
        $data = $request->validated();

        $transaction = $this->makePayment($stripeService, $data);
    }

    public function makePayment(PaymentGateway $paymentGateway, array $data)
    {
        $response = $paymentGateway->charge($data);

        $data['charge_id'] = $response->id;

        auth()->user()->addTransaction($data);

        return $data;
    }

    public function refundPayment(PaymentGateway $paymentGateway, string $chargeId)
    {
        return $paymentGateway->refund($chargeId);
    }
}
