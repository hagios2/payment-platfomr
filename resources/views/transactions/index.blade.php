@extends('layouts.user_type.auth')
@section('content')
    <div>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0">Payment Transactions</h5>
                            </div>
                            <div>
                                <a href="javascript:void(0)" class="btn bg-gradient-info btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#exampleModal1" type="button">+&nbsp; Add a Card</a>
                                <a href="javascript:void(0)" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#exampleModal" type="button">+&nbsp; Add a Charge</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        @include('includes.error')
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Charge
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Reference
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Amount
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Currency
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Type
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Status
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{$transaction->charge_id}}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{$transaction->reference}}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($transaction->amount, 2)}}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ strtoupper($transaction->currency) }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ strtoupper($transaction->type) }}</p>
                                        </td>
                                        <td class="text-center">
                                            @if ($transaction->status === 'success')
                                                <span class="text-xs font-weight-bold mb-0 badge bg-gradient-success">Success</span>
                                            @elseif ($transaction->status === 'refunded')
                                                    <span class="text-xs font-weight-bold mb-0 badge bg-gradient-info">Refunded</span>
                                            @elseif ($transaction->status === 'pending')
                                                <span class="text-xs font-weight-bold mb-0 badge bg-gradient-warning">Pending</span>
                                            @else
                                                <span class="text-xs font-weight-bold mb-0 badge bg-gradient-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
{{--                                            <a href="javascript:void(0);" class="mx-1 btn " data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="editPaymentMethod({{$transaction->id}})"--}}
{{--                                               data-bs-original-title="Edit Payment Method">--}}
{{--                                                <i class="cursor-pointer fas fa- text-info"></i>--}}
{{--                                            </a>--}}
                                            @if ($transaction->type === 'charge' && $transaction->status === 'success')
                                                <a href="{{route('refund.charge', $transaction->id)}}" onclick="event.preventDefault(); confirm('Are you sure you want to refund this transaction') ? document.getElementsByClassName('payment-method-destroy')[0].submit() : ''" class="mx-1 btn" data-bs-toggle="tooltip"
                                                   data-bs-original-title="Refund Transaction">
                                                    <i class="cursor-pointer fas fa-undo text-danger"></i>

                                                    <form class="payment-method-destroy" action="{{ route('refund.charge', $transaction->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                    </form>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                                </tbody>
                            </table>
                            @if($transactions->count() > 0)
                                {{$transactions->links('vendor.pagination.custom')}}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Add a charge</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{route('add.charge')}}">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 col-sm-12">
                                    <label>Payment Methods</label>
                                    <select name="payment_method" class="form-control" id="exampleFormControlSelect1" >
                                        <option value="">Select Method</option>
                                        <option value="momo">Mobile Money</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <label>Currency</label>
                                    <select name="currency" class="form-control" id="exampleFormControlSelect1" >
                                        <option value="">Select Currency</option>
                                        <option value="usd">USD</option>
                                        <option value="gbp">GBP</option>
                                        <option value="eur">EUR</option>
                                        <option value="ngn">NGN</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="form-check form-switch">
                                        <label>Amount</label> <br>
                                        <input name="amount" class="form-control" type="number"  placeholder="3000">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" placeholder="Enter description" >
                                    </textarea>
                                </div>
                            </div>
                            <div class="text-left mt-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Add a Card</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{route('add.card')}}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <label>Card Number</label>
                                    <input name="number" class="form-control" type="number"  placeholder="42000000000000">
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <label>Exp Year(YYYY)</label>
                                    <input name="exp_year" class="form-control" type="number"  placeholder="2015">
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <label>Exp Month(MM)</label>
                                    <input name="exp_month" class="form-control" type="number"  placeholder="11">
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <label>CVC</label>
                                    <input name="cvc" class="form-control" type="number"  placeholder="123">
                                </div>
                            </div>
                            <div class="text-left mt-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>
        $('#flexSwitchCheckDefault').on('click', function () {
            if ($(this).prop('checked')) {
                alert('me here')
                $('#flexSwitchCheckDefault').val(1);
            } else {
                alert('me there')
                $('#flexSwitchCheckDefault').val(0);
            }
        });

        function editPaymentMethod(id)
        {
            $.ajax({
                url: `{{route('payment-method.show', 'change_me')}}`.replace('change_me', id)
            }).done(function (data){
                console.log('data', data)

                const checked = data.payment_method.is_default ? `checked` : ``
                const checkedValue = data.payment_method.is_default ? 1 : 0

                const cardSelection = data.payment_method.payment_method === `card` ? `selected` : ``
                const momoSelection = data.payment_method.payment_method === `momo` ? `selected` : ``
                const url = `{{route('payment-method.update', 'change_me')}}`.replace('change_me', id)

                let dom =
                    `<form method="POST" action="${url}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label>Payment Methods</label>
                            <select name="payment_method" class="form-control" id="exampleFormControlSelect1" >
                                <option value="">Select Method</option>
                                <option ${momoSelection} value="momo">Mobile Money</option>
                                <option ${cardSelection} value="card">Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <label>Make Default</label> <br>
                                <input ${checked} name="is_default" class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" value="${checkedValue}">
                                <label class="form-check-label" for="flexSwitchCheckDefault">Set as default</label>
                            </div>
                        </div>
                    </div>
                    <div class="text-left mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>`

                $('.modal-body').html(dom)
            })
        }
    </script>
@endsection
