<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function storeCustomerData($customerData): void
    {
        $user = User::find(1);

        $user->update($customerData);
    }
}
