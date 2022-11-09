<?php

namespace App\Repositories;

class UserRepository
{
    public function storeCustomerData($customerData): void
    {
        auth()->user()->update($customerData);
    }
}
