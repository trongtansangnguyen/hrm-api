<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/users', function () {
    return view('create-user');
})->name('users');

Route::get('/example', function () {
    return view('example');
})->name('example');

Route::get('/login-session', function () {
    return view('login-session');
})->name('login.session');

Route::post('/login-session', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        return back()->withInput($request->only('email'))->with('error', 'Sai email hoac mat khau.');
    }

    $request->session()->regenerate();

    return redirect()->route('dashboard.session');
})->name('login.session.submit');

Route::get('/dashboard-session', function () {
    if (!Auth::check()) {
        return redirect()->route('login.session');
    }

    return response()->view('dashboard-session', [
        'user' => Auth::user(),
    ]);
})->name('dashboard.session');

Route::post('/logout-session', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login.session');
})->name('logout.session');

Route::post('/users', function () {
    // Handle form submission and create a new user
    // You can access form data using request()->input('field_name')
    // For example:
    $full_name = request()->input('fullname');
    $email = request()->input('email');
    // Here you would typically save the user to the database
    // User::create(['name' => $name, 'email' => $email]);

    return redirect('/users')->with('success', 'User created successfully!');
});

Route::get('/real-time', function () {
    return view('real-time');
});