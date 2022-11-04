<?php

namespace App\Repositories;

use App\Http\Requests\PaymentMethodRequest;
use App\Models\UserPaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserPaymentMethodRepository
{
    public function getUserPaymentMethods(): Collection|array
    {
        return UserPaymentMethod::query()
            ->where('user_id', auth()->id())->get();
    }

    public function findById(int $id)
    {
        return UserPaymentMethod::find($id);
    }

    public function getDefaultPaymentMethod(): Model|Builder|null
    {
        return UserPaymentMethod::query()
            ->where('user_id', auth()->id())
            ->where('is_default', true)
            ->first();
    }

    public function storePaymentMethod(PaymentMethodRequest $request)
    {
        return auth()->user()->addPaymentMethod($request->validated());
    }

    public function updatePaymentMethod(
        PaymentMethodRequest $request,
        int $id
    ): UserPaymentMethod
    {
        $paymentMethod = UserPaymentMethod::find($id);

        $paymentMethod->update($request->validated());

        return $paymentMethod;
    }

    public function deletePaymentMethod(int $id): void
    {
        UserPaymentMethod::query()
            ->where('id', $id)
            ?->delete();
    }
}
