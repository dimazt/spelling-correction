@extends('index')
@section('title', 'Beranda')
@section('content')

<div class="card card-shadow mt-3 p-5">
    <h5>Upload PDF untuk Spelling Correction</h5>
    <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="pdf" accept="application/pdf" required>
        <button type="submit" class="btn btn-primary">Upload PDF</button>
    </form>

</div>


@endsection