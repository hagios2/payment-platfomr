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

    public function storeTransaction($data, string $status, string $type): void
    {
        $data['reference'] = 'REF/STR/' . rand(10000, 9999999);
        $data['type'] = $type;
        $data['status'] = $status;

        auth()->user()->addTransaction($data);
    }

    public function updateTransactionStatusToRefunded(int $id): void
    {
        Transaction::query()
            ->where('id', $id)
            ?->update(['status' => 'refunded']);
    }

    public function fetchTransactions(): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('user_id', auth()->id())
            ->latest()->paginate(5);
    }
}
