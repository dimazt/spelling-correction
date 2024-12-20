@extends('auth.layout')
@section('auth')
<div class="p-5">
    <div class="text-center">
        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
    </div>
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <form class="user" action="{{route('login')}}" method="POST">
        @csrf
        <div class="form-group">
            <input type="email" class="form-control form-control-user" name="email" aria-describedby="emailHelp"
                placeholder="Enter Email Address...">
        </div>
        <div class="form-group">
            <input type="password" class="form-control form-control-user" name="password" placeholder="Password">
        </div>
        <div class="form-group">
            <div class="custom-control custom-checkbox small">
                <input type="checkbox" class="custom-control-input" id="customCheck">
                <label class="custom-control-label" for="customCheck">Remember
                    Me</label>
            </div>
        </div>
        <button class="btn btn-primary btn-user btn-block" type="submit">
            Login
        </button>

    </form>
    <hr>
    <div class="text-center">
        <a class="small" href={{route('register.page')}}>Create an Account!</a>
    </div>
</div>
@endsection