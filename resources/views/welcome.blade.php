<!DOCTYPE html>
<html>
<head>
    <title>Upload PDF untuk Spelling Correction</title>
</head>
<body>
    <h1>Upload PDF untuk Spelling Correction</h1>
    <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="pdf" accept="application/pdf" required>
        <button type="submit">Upload PDF</button>
    </form>
</body>
</html>
