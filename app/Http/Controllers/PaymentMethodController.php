<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentMethodRequest;
use App\Repositories\UserPaymentMethodRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(private UserPaymentMethodRepository $repository)
    {
    }

    public function index()
    {
        $paymentMethods = $this->repository->getUserPaymentMethods();

        return view('', compact('paymentMethods'));
    }

    public function create(): Factory|View|Application
    {
        return view('');
    }

    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        $this->repository->storePaymentMethod($request);

        return redirect()->route('');
    }

    public function show($id)
    {

    }


    public function edit($id): Factory|View|Application
    {
        $paymentMethod = $this->repository->findById($id);

        return view('', $paymentMethod);
    }


    public function update(PaymentMethodRequest $request, $id): RedirectResponse
    {
        $this->repository->updatePaymentMethod($request, $id);

        return redirect()->route('');
    }


    public function destroy($id): RedirectResponse
    {
        $this->repository->deletePaymentMethod($id);

        return redirect()->route('');
    }
}
