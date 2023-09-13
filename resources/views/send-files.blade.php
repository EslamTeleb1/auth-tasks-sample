<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Files</title>
</head>
<body>
    <h1>Upload XML Files</h1>

     <a class="btn" href="{{route('send')}}"> send files</a>
    <form action="http://127.0.0.1:8005/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" multiple>
        <button class="btn" type="submit">send the xml files</button>
    </form>
</body>
</html>
