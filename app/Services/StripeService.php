<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Interfaces\PaymentGateway;
use Stripe\BankAccount;
use Stripe\Card;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Refund;
use Stripe\Source;
use Stripe\StripeClient;
use Stripe\Token;

class StripeService implements PaymentGateway
{
    public function __construct(
        private StripeClient $stripeClient,
        private UserRepository $userRepository
    )
    {
        $this->stripeClient = new StripeClient(env('STRIPE_SEC_KEY'));
    }

    public function charge($paymentData): Charge
    {
        $user = User::find(1);

        $paymentData['customer'] = $user->fresh()->customer_id;

        return $this->stripeClient->charges->create($paymentData);
    }

    public function refund($chargeId): Refund
    {
        return $this->stripeClient->refunds->create([
            'charge' => $chargeId,
        ]);
    }

    public function createCustomer($user, $cardDetails)
    {
        $customer = $this->stripeClient->customers->create([
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $token = $this->createToken($cardDetails);

        $this->userRepository->storeCustomerData([
            'customer_id' => $customer->id,
            'source' => $token->id
        ]);
    }

    public function createToken($cardDetails): Token
    {
        return $this->stripeClient->tokens->create([
            'card' => $cardDetails
        ]);
    }

    public function createCard(string $customerId)
    {
        $card = $this->stripeClient->customers->createSource(
            $customerId,
            ['source' => 'test_work']
        );

        dump($card);

//        $this->userRepository->storeCustomerData([
//            'source' => $card->source
//        ]);
    }
}
