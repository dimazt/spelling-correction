<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') | Spelling Correction</title>

    <link href="{{asset('assets/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.css" />
    <!-- Custom styles for this template-->
    <link href="{{asset('assets/css/sb-admin-2.min.css')}}" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        .background-gradient {
            background: linear-gradient(to bottom, #2c73d2, #0081cf, #0089ba);
            color: #fff;
            align-items: center;
            justify-content: center;
            text-align: center;

        }

        .previewContainer {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 10px;
        }

        .previewContainerImage {
            width: 100%;
            height: auto;
        }

        #moreOverlay {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            font-size: 24px;
            position: relative;
        }

        table {
            width: 100%;
            table-layout: fixed;
            /* Optional: Force equal width columns */
        }

        th,
        td {
            padding: 8px 16px;
            /* Padding untuk memberi ruang dalam cell */
            text-align: left;
            /* Align teks ke kiri */
        }

        #suggestions {
            border: 1px solid red;
            padding: 10px;
            margin-top: 10px;
            background-color: #fff3f3;
            /* Warna latar belakang */
        }
    </style>
</head>

<body>
    <div class="container-fluid p-3 shadow" style="background-color: #d4e5ed;">
        <header>
            <!-- JUDUL -->
            <div class="background-gradient p-2">
                <div class="row">
                    <div class="col-2">
                        <img style="width: 120px; " src="{{asset('assets/logo.png')}}" alt="">
                    </div>
                    <div class="col-9" style="text-align: center;">
                        <strong>
                            <span style="font-size: 24px;">SISTEM SPELLING CORRECTION</span><br>
                        </strong>
                        <strong>
                            <span style="font-size: 24px;">MENGGUNAKAN METODE LEVENSHTEIN DISTANCE</span><br>
                        </strong>
                        <strong>
                            <span style="font-size: 24px;">PADA DOKUMEN BERBAHASA INDONESIA</span>
                        </strong>
                    </div>
                </div>
            </div>
            <!-- SUSUNAN MENU -->
            <div id="navbar" class="btn-group mt-2">
                @php if (!isset($active_page)) {$active_page = "";} @endphp
                <a href="{{ route('home') }}" class="btn btn-primary {{$active_page == "home" ? 'active' : ''}}"
                    aria-current="page">Home</a>
                <a href="{{ route('training') }}" class="btn btn-primary {{$active_page == "training" ? 'active' : ''}}"
                    aria-current="page">Training</a>
                <a href="{{ route('kamus') }}" class="btn btn-primary {{$active_page == "kamus" ? 'active' : ''}}"
                    aria-current="page">Kamus</a>

            </div>
        </header>
        <main>
            @yield('content')
        </main>

        @yield('scripts')
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script src="https://cdn.datatables.net/2.1.7/js/dataTables.js"></script>
<script>
    // $(document).ready(function () {
    //     // $('#datatable').DataTable();
    //     let newTable = new DataTable('#datatable', {
    //         responsive: true
    //     })
    // });
</script>

</html>