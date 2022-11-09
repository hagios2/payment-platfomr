<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardDetailsRequest;
use App\Http\Requests\PaymentRequest;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\Interfaces\PaymentGateway;
use App\Services\StripeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(private UserRepository $userRepository)
    {
        $this->middleware('auth');
    }

    public function index(TransactionRepository $repository): Factory|View|Application
    {
        $transactions = $repository->fetchTransactions();

        return view('transactions.index', compact('transactions'));
    }

    public function addCardDetails(CardDetailsRequest $request, StripeService $stripeService): RedirectResponse
    {
        $customerData = $stripeService->createCustomer(auth()->user(), [
            'card' => $request->validated()
        ]);

        $this->userRepository->storeCustomerData($customerData);

        return redirect()->route('transactions')->with('success', 'card details added successfully');
    }

    public function initStripePayment(PaymentRequest $request, StripeService $stripeService, TransactionRepository $repository): RedirectResponse
    {
        if (!auth()->user()->customer_id && !auth()->user()->source)
        {
            return back()->with('error', 'Card details required! Please a add card first');
        }

        $data = $request->validated();

        try {
            $transaction = $this->makePayment($stripeService, $data);

            $repository->storeTransaction($transaction, 'success', 'charge');

            return redirect()->route('transactions')->with('success', 'Charge was successful');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function refundStripePayment(int $id, TransactionRepository $repository, StripeService $stripeService): RedirectResponse
    {
        $transaction = $repository->findById($id);
        $newTransaction = $transaction->toArray();
        try {
            DB::beginTransaction();
            $this->refundPayment($stripeService, $transaction->charge_id);

            //add a new transaction for the refund. this is for the account which credited during a refund
            $repository->storeTransaction($newTransaction, 'success', 'refund');

            //add a new transaction for the refund. this is for the account which debited during the refund
            $repository->updateTransactionStatusToRefunded($id);

            DB::commit();
            return redirect()->route('transactions')->with('success', 'Refund was successful');
        } catch (\Exception $e) {
            //rollback back initial commits if theres failure
            DB::rollBack();

            //add a transaction record that there was a failed attempt
            $repository->storeTransaction($newTransaction, 'failed', 'refund');

            return redirect()->route('transactions')->with('error', $e->getMessage());
        }
    }

    public function makePayment(PaymentGateway $paymentGateway, array $data): array
    {
        $data['charge_id'] = $paymentGateway->charge($data);

        return $data;
    }

    public function refundPayment(PaymentGateway $paymentGateway, string $chargeId)
    {
        return $paymentGateway->refund($chargeId);
    }
}
