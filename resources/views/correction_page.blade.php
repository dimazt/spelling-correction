@extends('index')
@section('title', "Correction")
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
                <h4 style="font-weight: 800; text-align:center" class="text text-bold">Hasil Koreksi</h4>
            </div>
            <div class="m-3">
                <h5 style="font-weight: 800; text-align:center" class="text text-bold">Nama Dokumen : @php
                    echo explode('.', $document->name)[0];
                @endphp
                </h5>
            </div>
            <form action="{{route('correction.update')}}" method="POST">
                @csrf
                <input type="hidden" name="document_id" value={{$document->id}}>
                <button class="btn btn-primary mb-3" type="submit">Simpan</button>
                <a href="/download/{{basename($document->result) }}"
                    class="btn btn btn-success mb-3 btn-icon-split {{ $document->status == "done" ? '' : 'disabled' }}">
                    <span class="icon text-white-60">
                        Download
                    </span>
                </a>

                <div id="editor">{!! $result !!}</div>
                <input type="hidden" name="content" id="content">
            </form>
        </div>
    </div>
</div>





@endsection

@section('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike', 'blockquote', 'link'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
        [{ 'script': 'sub' }, { 'script': 'super' }],
        [{ 'indent': '-1' }, { 'indent': '+1' }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
        [{ 'align': [] }],

        ['clean']
    ];
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions
        }
    });

    document.querySelector('form').onsubmit = function () {
        var content = document.querySelector('#content');
        content.value = quill.root.innerHTML;
    };

    // Mengubah warna teks dari dropdown
    document.querySelector('#color-select').addEventListener('change', function () {
        var color = this.value;
        var range = quill.getSelection();
        if (range) {
            quill.formatText(range.index, range.length, { 'color': color });
        }
    });

    // Mengubah alignment teks dari dropdown
    document.querySelector('#align-select').addEventListener('change', function () {
        var align = this.value;
        quill.format('align', align);
    });
</script>
@endsection