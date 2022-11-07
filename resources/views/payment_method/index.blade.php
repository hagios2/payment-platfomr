@extends('layouts.user_type.auth')
@section('content')
    <div>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0">Payment Methods</h5>
                            </div>
                            <a href="javascript:void(0)" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#exampleModal" type="button">+&nbsp; New Member</a>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                     @include('includes.error')
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Method
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Default
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($paymentMethods as $paymentMethod)
                                    <tr>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{$paymentMethod->payment_method}}</p>
                                        </td>
                                        <td class="text-center">
                                            @if ($paymentMethod->is_default)
                                                <span class="text-xs font-weight-bold mb-0 badge bg-gradient-success">default</span>
                                            @else
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:void(0);" class="mx-1 btn " data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="editPaymentMethod({{$paymentMethod->id}})"
                                               data-bs-original-title="Edit Payment Method">
                                                <i class="cursor-pointer fas fa-edit text-info"></i>
                                            </a>
                                            <a href="{{route('payment-method.destroy', $paymentMethod->id)}}" onclick="event.preventDefault(); confirm('Are you sure you want to delete this member') ? document.getElementsByClassName('payment-method-destroy')[0].submit() : ''" class="mx-1 btn" data-bs-toggle="tooltip"
                                               data-bs-original-title="Delete ">
                                                <i class="cursor-pointer fas fa-trash text-danger"></i>

                                                <form class="payment-method-destroy" action="{{ route('payment-method.destroy', $paymentMethod->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                                </tbody>
                            </table>
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
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Add Payment Method</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{route('payment-method.store')}}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Payment Methods</label>
                                    <select name="payment_method" class="form-control" id="exampleFormControlSelect1" >
                                        <option value="">Select Method</option>
                                        <option value="momo">Mobile Money</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <label>Make Default</label> <br>
                                        <input name="is_default" class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" value="0">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">Set as default</label>
                                    </div>
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
