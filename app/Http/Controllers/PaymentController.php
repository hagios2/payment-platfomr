<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardDetailsRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\User;
use App\Repositories\TransactionRepository;
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

    public function initStripePayment(PaymentRequest $request, StripeService $stripeService, TransactionRepository $repository)
    {
        $data = $request->validated();

        $transaction = $this->makePayment($stripeService, $data);

        $repository->storeTransaction($transaction, 'success', 'charge');

        return response()->json(['message' => 'success']);
    }

    public function refundStripePayment(int $id, TransactionRepository $repository, StripeService $stripeService)
    {
        $transaction = $repository->findById($id);

        try {
            $refund = $this->refundPayment($stripeService, $transaction->charge_id);
            $newTransaction = $transaction->toArray();

            $repository->storeTransaction($newTransaction, 'success', 'refund');
            return response()->json(['message' => 'success']);
        } catch (\Exception $e) {
            $newTransaction = $transaction->toArray();

            $repository->storeTransaction($newTransaction, 'failed', 'refund');
            return response()->json(['message' => 'failed']);
        }
    }

    public function makePayment(PaymentGateway $paymentGateway, array $data)
    {
        $response = $paymentGateway->charge($data);

        $data['charge_id'] = $response->id;

        return $data;
    }

    public function refundPayment(PaymentGateway $paymentGateway, string $chargeId)
    {
        return $paymentGateway->refund($chargeId);
    }
}
