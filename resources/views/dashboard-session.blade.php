<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Session Example</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <h1>Dashboard Session</h1>
    <p>Ban da dang nhap bang session.</p>
    <p>Email: {{ $user->email }}</p>

    <form method="POST" action="{{ route('logout.session') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
