<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\Interfaces\PaymentGateway;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\StripeClient;
use Stripe\Token;

class StripeService implements PaymentGateway
{
    public function __construct(private StripeClient $stripeClient)
    {
        $this->stripeClient = new StripeClient(env('STRIPE_SEC_KEY'));
    }

    public function charge($paymentData): string
    {
        $paymentData['customer'] = auth()->user()->customer_id;
        $charge = $this->stripeClient->charges->create($paymentData);

        return $charge->id;
    }

    public function refund($chargeId): string
    {
        $refund = $this->stripeClient->refunds->create(['charge' => $chargeId]);

        return $refund->status;
    }

    public function createCustomer($user, $cardDetails): array
    {
        $customer = $this->stripeClient->customers->create([
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $token = $this->createToken($cardDetails);

        $this->createCard($customer->id, $token);

        return [
            'customer_id' => $customer->id,
            'source' => $token
        ];
    }

    public function createToken($cardDetails): string
    {
        $token = $this->stripeClient->tokens->create($cardDetails);

        return $token->id;
    }

    public function createCard(string $customerId, string $source)
    {
        $this->stripeClient->customers->createSource(
            $customerId,
            ['source' => $source]
        );
    }
}
