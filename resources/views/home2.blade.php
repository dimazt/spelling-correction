@extends('index')
@section('title', $active_page == "training" ? "Training" : "Beranda")
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
    <div class="m-3">
        <h4 style="font-weight: 800; text-align:center" class="text text-bold">Upload PDF Untuk Spelling
            Correction</h4>
    </div>
    <div class="row-6 justify-center text-center">
        <div class="col">
            <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="pdf" accept="application/pdf" required>
                <input type="hidden" name="type" value={{$active_page}}>
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
            <table id="correctionTable" class="table table-bordered table-auto table-hover w-100">
                <thead style="font-weight: 800">
                    <tr>
                        <th width="7%" style="text-align:center">No</th>
                        <th>Nama Dokumen</th>
                        <th width="10%">Ukuran File</th>
                        <th>Tanggal Upload</th>
                        <th>Selesai Pada</th>
                        <th>Status</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
    </div>
</div>


@endsection

@section('scripts')

<script>
    $(document).ready(function () {
        let table = $('#correctionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('correction.result', ['active_page' => $active_page]) }}",
                type: 'GET'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'name', name: 'name' },
                { data: 'file_size', name: 'file_size' },
                { data: 'upload_date', name: 'upload_date' },
                { data: 'completed_date', name: 'completed_date' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            autoWidth: false
        });

        // Refresh data every 5 seconds
        setInterval(() => {
            table.ajax.reload(null, false); // false to avoid resetting pagination
        }, 5000);
    });
</script>


@endsection