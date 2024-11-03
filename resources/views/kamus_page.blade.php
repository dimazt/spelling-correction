@extends('index')
@section('title', 'Beranda')
@section('content')
<div style="min-width: 100%; margin:0" class="card card-shadow mt-3 p-5 ">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif (session('failed'))
        <div class="alert alert-danger">
            {{ session('failed') }}
        </div>
    @endif
    <div class="row">
        <div class="col">
            <div class="m-3">
                <h4 style="font-weight: 800; text-align:center" class="text text-bold">Data Kamus</h4>
            </div>
            <table id="kamusKBBITable" class="table table-bordered table-auto table-hover w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kata</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#kamusKBBITable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('list.kamus') }}',
            columns: [
                {
                    data: null,
                    name: 'No',
                    orderable: false,
                    searchable: false,
                    className: 'text text-left',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'word', name: 'word' },
            ],
            pageLength: 10, // Menentukan jumlah item per halaman
        });
    });
</script>
@endsection