@extends('auth.layout')
@section('auth')
<div class="p-5">
    <div class="text-center">
        <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
    </div>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form class="user" action="{{route('register')}}" method="POST">
        @csrf
        <div class="form-group">
            <input type="text" name="name" class="form-control form-control-user" placeholder="Nama Lengkap">
        </div>
        <div class="form-group">
            <input type="email" name="email" class="form-control form-control-user" placeholder="Email">
        </div>
        <div class="form-group row">
            <div class="col-sm-6 mb-3 mb-sm-0">
                <input type="password" name="password" class="form-control form-control-user" placeholder="Password">
            </div>
            <div class="col-sm-6">
                <input type="password" name="password_confirmation" class="form-control form-control-user"
                    placeholder="Konfirmasi Password">
            </div>
        </div>
        <button class="btn btn-primary btn-user btn-block" type="submit">
            Register Account
        </button>

    </form>
    <hr>
    <div class="text-center">
        <a class="small" href={{route('login.page')}}>Already have an account? Login!</a>
    </div>
</div>
@endsection