@extends('index')
@section('title', 'Beranda')
@section('content')

<div style="min-width: 100%; margin:0" class="card card-shadow mt-3 p-5 ">
    <div class="m-3">
        <h4 style="font-weight: 800; text-align:center" class="text text-bold">Upload PDF Untuk Spelling
            Correction</h4>
    </div>
    <div class="row-6 justify-center text-center">
        <div class="col">
            <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="pdf" accept="application/pdf" required>
                <button type="submit" class="btn btn-primary">Upload PDF</button>
            </form>
        </div>
    </div>

    <br>
    <hr>
    <div class="row">
        <div class="col">
            <div class="m-3">
                <h4 style="font-weight: 800; text-align:center" class="text text-bold">Hasil Koreksi</h4>
            </div>
            <table id="datatable" class="table table-bordered table-auto table-hover w-100">
                <thead style="font-weight: 800">
                    <td width="100" style="text-align:center">
                        No
                    </td>
                    <td>
                        Nama Dokumen
                    </td>
                    <td>
                        Tanggal Upload
                    </td>
                    <td>
                        Status
                    </td>
                    <td>
                        Action
                    </td>
                </thead>
                <tbody>
                    @foreach (['1', '2', '3'] as $item)
                        <tr>
                            <td style="text-align:center">{{ $item }}</td>
                            <td>Gatau Namanya apa </td>
                            <td>2 September 2021 </td>
                            <td class="text-center"><span  class="btn btn-sm btn-success disabled">Success</span></td>
                            <td class="text text-center">
                                <a href="#"
                                    class="btn btn-sm btn-success btn-icon-split {{isset($item['status']) ? '' : 'disabled'}}">
                                    <span class="icon text-white-60">
                                        <i class="fas fa-download"></i>
                                    </span>
                                </a>
                                <a href="#" class="btn btn-sm btn-warning btn-icon-split">
                                    <span class="icon text-60">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger btn-icon-split">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection