<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentMethodRequest;
use App\Repositories\UserPaymentMethodRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PaymentMethodController extends Controller
{
    public function __construct(private UserPaymentMethodRepository $repository)
    {
        $this->middleware('auth');
    }

    public function index(): Factory|View|Application
    {
        $paymentMethods = $this->repository->getUserPaymentMethods();

        return view('payment_method.index', compact('paymentMethods'));
    }

    public function create()
    {}

    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        if ($this->repository->getExistingPaymentMethod($request->safe()->payment_method)) {
            return back()->with('error', 'The selected payment method has already been added');
        }

        $newPaymentMethod = $this->repository->storePaymentMethod($request);

        if ($request->filled('is_default') && $request->safe()->is_default) {
            $this->repository->removeDefault($newPaymentMethod->id);
        }

        return redirect()->route('payment-method.index')->with('success', 'Payment Method saved successfully');
    }

    public function show($id): JsonResponse
    {
        return response()->json(['payment_method' => $this->repository->findById($id)]);
    }

    public function edit($id)
    {
    }

    public function update(PaymentMethodRequest $request, $id): RedirectResponse
    {
        $this->repository->updatePaymentMethod($request, $id);

        if ($request->filled('is_default') && $request->safe()->is_default) {
            $this->repository->removeDefault($id);
        }

        return redirect()->route('payment-method.index')->with('success', 'Payment method updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $this->repository->deletePaymentMethod($id);

        return redirect()->route('payment-method.index')->with('success', 'Payment Method Deleted Successfully');
    }
}
