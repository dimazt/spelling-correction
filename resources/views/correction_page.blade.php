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



                <div id="editor" contenteditable="true">{!! $result !!}</div>
                <input type="hidden" name="content" id="content">
                <div id="suggestions" style="color: red;"></div>

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



    let timeout;

    document.getElementById('editor').addEventListener('input', function () {
        clearTimeout(timeout); // Hapus timeout sebelumnya
        timeout = setTimeout(async () => {
            const content = this.innerText;
            const suggestions = await getSuggestions(content);
        }, 1000); // Tunggu 1 detik setelah pengguna berhenti mengetik
    });

    async function getSuggestions(text) {
        try {
            const url = 'https://api.languagetool.org/v2/check';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    text: text,
                    language: 'en-US', // Ganti dengan 'id' untuk Bahasa Indonesia
                }),
            });

            console.log(response);

            // Periksa status respons
            if (!response.ok) {
                const errorResponse = await response.text(); // Ambil respons kesalahan untuk analisis
                console.error('Error response:', errorResponse);
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            displaySuggestions(data.matches)
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }


    function displaySuggestions(suggestions) {
        const suggestionsDiv = document.getElementById('suggestions');
        console.log(suggestions);
        console.log(typeof suggestions);

        suggestionsDiv.innerHTML = ''; // Clear previous suggestions
        // if (!suggestions) {
        //     suggestionsDiv.innerHTML = '<div>Tidak ada saran perbaikan.</div>'; // Pesan jika tidak ada saran
        //     return;
        // }
        suggestions.forEach(suggestion => {
            console.log(suggestion);

            suggestionsDiv.innerHTML += `<div>Kesalahan: "${suggestion.context.text}" - Saran: ${suggestion.replacements.map(rep => rep.value).join(', ')}</div>`;
        });
    }

</script>
@endsection