<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
</head>
<body>
    <form method="POST" action="/users">
        @csrf

        <label>Họ và tên:</label>
        <input type="text" name="fullname">
        <br>
        <label>Email:</label>
        <input type="email" name="email">
        <br>
        <button type="submit">Gửi thông tin</button>
    </form>
</body>
</html>
</form>
