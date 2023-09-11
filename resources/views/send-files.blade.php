<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Files</title>
</head>
<body>
    <h1>Upload XML Files</h1>
    <form action="{{ route('send-files') }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- <input type="file" name="files[]" multiple> --}}
        <button class="btn " type="submit">send the xml files</button>
    </form>
</body>
</html>
