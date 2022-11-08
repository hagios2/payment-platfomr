<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository
{
    public function findById(int $id)
    {
        return Transaction::find($id);
    }

    public function storeTransaction($data, string $status, string $type)
    {
        $user = User::find(1);

        $data['reference'] = 'REF/STR/' . rand(10000, 9999999);
        $data['type'] = $type;
        $data['status'] = $status;

        $user->addTransaction($data);
    }

    public function fetchTransactions(): LengthAwarePaginator
    {
        $user = User::find(1);

        return Transaction::query()
            ->where('user_id', $user->id)
            ->latest()->paginate(15);
    }
}
