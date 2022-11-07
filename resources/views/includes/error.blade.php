@if (session()->has('success'))
    <div class="alert alert-success" role="alert">
        <strong>Success!</strong> {{session('success')}}
    </div>
@endif
@if (session()->has('error'))
    <div class="alert alert-danger" role="alert">
        <strong>Error!</strong> {{session('error')}}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
