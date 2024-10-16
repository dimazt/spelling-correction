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
    </style>
</head>

<body>
    <div class="container p-3 shadow" style="background-color: #d4e5ed;">
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
                <a href="{{ route('home') }}" class="btn btn-primary {{$active_page == "home" ? 'active' : ''}}"
                    aria-current="page">Home</a>
                <a href="{{ route('training') }}" class="btn btn-primary {{$active_page == "training" ? 'active' : ''}}"
                    aria-current="page">Training</a>
                <a href="{{ route('setting') }}" class="btn btn-primary {{$active_page == "setting" ? 'active' : ''}}"
                    aria-current="page">Kamus</a>

            </div>
        </header>
        <main>
            @yield('content')
        </main>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#extract-button').click(function () {
            extract()
        });
    });
    $(document).ready(function () {
        $('#save-knn').click(function () {
            let knn = $('#knn-value').val()
            console.log(knn);
            $.ajax({
                url: '/save-knn',
                type: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({ k_value: knn }),

                success: function (response) {
                    console.log(response);

                }
            });
        });
    });

    $(document).ready(function () {
        $('#fileInput').change(function (event) {
            var file = event.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#previewImage').attr('src', e.target.result);
                    userRole = JSON.parse(localStorage.getItem('user'));
                    if (userRole.role === 'user') {
                        extract();
                        identify();
                    }

                };
                reader.readAsDataURL(file);
            }
        });
    });

    $(document).ready(function () {
        $('#identifyBtn').click(function () {
            identify()
        });
    });

    $.ajax({
        url: '/is-login',
        type: 'GET',
        success: function (response) {
            console.log(response);
            var navbar = $('#navbar')
            if (!response.isLogin) {
                navbar.remove()
            }
            if (response.user && response.user.role == 'user') {
                $('.adminOnly').remove()
            }
            if (response.user) {
                localStorage.setItem('user', JSON.stringify(response.user))
                $('#namaUser').text(response.user.fullname)
            }
        }
    });
    function extract() {
        var formData = new FormData();
        formData.append('test_file', $('input[name="test_file"]')[0].files[0]);
        $.ajax({
            url: '/ekstraksi', // Ganti dengan endpoint API yang sesuai
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                var tbody = $('#feature-table-body');
                tbody.empty(); // Kosongkan tabel

                $.each(response.features, function (index, feature) {
                    tbody.append('<tr><td>' + (index + 1) + '</td><td>' + feature.name + '</td><td>' + feature.value + '</td></tr>');
                });
                $('#processed-image').attr('src', response.image_url);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }


    function identify() {
        var formData = new FormData($('#identificationForm')[0]);
        $.ajax({
            url: '/identifikasi',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {

                if (response.result === "Berformalin") {
                    $('#resultCard').removeClass('border-bottom-success')
                        .addClass('border-bottom-danger')
                        .show();
                    $('#resultText').text("Daging Ayam Berformalin");
                } else {
                    $('#resultCard').removeClass('border-bottom-danger')
                        .addClass('border-bottom-success')
                        .show();
                    $('#resultText').text("Daging Ayam Tidak Berformalin");
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    }

    $(document).ready(function () {
        $()
        $('#trainBtn').click(function () {
            var formData = new FormData($('#trainingForm')[0]);
            $.ajax({
                url: '/training-model',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    $('#progressContainer').show();
                    checkProgress();
                },
                error: function () {
                    alert('An error occurred during training.');
                    $('#progressContainer').hide();
                }
            });
        });
        $('#trainingForm').submit(function (event) {
            event.preventDefault(); // Mencegah pengiriman formulir secara default
        });
        function checkProgress() {
            var button = $('#trainBtn')
            button.addClass('disabled');
            $.ajax({
                url: '/training-status',
                type: 'GET',
                success: function (response) {
                    console.log('check' + response); // Tambahkan log untuk debugging
                    var progress = response.percentage;
                    $('#progressBar').css('width', progress + '%').text(progress + '%');

                    if (response.status === 'completed') {
                        alert('Training complete!');
                        $('#progressContainer').hide();
                        button.removeClass('disabled');
                    } else {
                        button.click(false)
                        setTimeout(checkProgress, 1000); // Check progress every second
                    }
                }
            });
        }

    });


    document.getElementById('formalinImageInput').addEventListener('change', function (event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('previewContainerFormalin');
        const defaultImage = document.getElementById('defaultImageFormalin');

        // Clear any previous preview images, including the default image
        previewContainer.innerHTML = '';

        if (files.length === 1) {
            // If only one image, use the default image style
            const file = files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    defaultImage.src = e.target.result;
                    defaultImage.style.height = '360px'
                    defaultImage.style.width = '520px'
                    defaultImage.style.objectFit = 'cover'
                    previewContainer.appendChild(defaultImage);
                }
                reader.readAsDataURL(file);
            }
        } else if (files.length > 1) {
            previewContainer.style.display = 'grid'
            previewContainer.style.gridTemplateColumns = 'repeat(3, 1fr)'
            previewContainer.style.gridGap = '10px'
            previewContainer.style.padding = '5px'


            // If more than one image, create a grid
            for (let i = 0; i < Math.min(files.length, 8); i++) {
                const file = files[i];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.height = '120px';
                        img.style.objectFit = 'cover';
                        img.classList.add('border', 'rounded');
                        img.alt = 'Gambar Tidak Muncul';
                        img.classList.add('previewContainerImage');
                        previewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            }
            // If there are more than 9 images, add the +more overlay
            if (files.length > 9) {
                const moreDiv = document.createElement('div');
                moreDiv.id = 'moreOverlay';
                moreDiv.style.height = '120px';
                moreDiv.style.objectFit = 'cover';
                moreDiv.classList.add('border', 'rounded');
                moreDiv.innerHTML = `<span>+${files.length - 9} more</span>`;

                previewContainer.appendChild(moreDiv);
            }
        } else {
            // If no images selected, show default image
            previewContainer.appendChild(defaultImage);
        }
    });

    document.getElementById('nonFormalinImageInput').addEventListener('change', function (event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('previewContainerNonFormalin');
        const defaultImage = document.getElementById('defaultImageNonFormalin');

        // Clear any previous preview images, including the default image
        previewContainer.innerHTML = '';

        if (files.length === 1) {
            // If only one image, use the default image style
            const file = files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    defaultImage.src = e.target.result;
                    defaultImage.style.height = '360px'
                    defaultImage.style.width = '520px'
                    defaultImage.style.objectFit = 'cover'
                    previewContainer.appendChild(defaultImage);
                }
                reader.readAsDataURL(file);
            }
        } else if (files.length > 1) {
            previewContainer.style.display = 'grid'
            previewContainer.style.gridTemplateColumns = 'repeat(3, 1fr)'
            previewContainer.style.gridGap = '10px'
            previewContainer.style.padding = '5px'


            // If more than one image, create a grid
            for (let i = 0; i < Math.min(files.length, 8); i++) {
                const file = files[i];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.height = '120px';
                        img.style.objectFit = 'cover';
                        img.classList.add('border', 'rounded');
                        img.alt = 'Gambar Tidak Muncul';
                        img.classList.add('previewContainerImage');
                        previewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            }
            // If there are more than 9 images, add the +more overlay
            if (files.length > 9) {
                const moreDiv = document.createElement('div');
                moreDiv.id = 'moreOverlay';
                moreDiv.style.height = '120px';
                moreDiv.style.objectFit = 'cover';
                moreDiv.classList.add('border', 'rounded');
                moreDiv.innerHTML = `<span>+${files.length - 9} more</span>`;

                previewContainer.appendChild(moreDiv);
            }
        } else {
            // If no images selected, show default image
            previewContainer.appendChild(defaultImage);
        }
    });

</script>

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.js"></script>
<script>
    $(document).ready(function () {
        // $('#datatable').DataTable();
        let newTable = new DataTable('#datatable', {
            responsive: true
        })
    });
</script>

</html>