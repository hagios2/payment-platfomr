<?php

namespace App\Services\Interfaces;

use Stripe\StripeClient;

interface PaymentGateway
{
    public function charge($paymentData);

    public function refund($chargeId);
}
