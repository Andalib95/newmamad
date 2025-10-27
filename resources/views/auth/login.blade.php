@extends('welcome')

    <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم</title>
</head>
<body>
<div class="container">
    <h1>ورود</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label for="email">ایمیل:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">رمز عبور:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">ورود</button>
    </form>
</div>
</body>
</html>
