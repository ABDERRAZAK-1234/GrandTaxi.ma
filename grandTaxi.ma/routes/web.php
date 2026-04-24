<?php

// use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;


Route::get('/index', function(){
    return view('index');
});

Route::get('/', fn() => view('auth.choose-role'));
Route::get('/login',    fn() => view('auth.login'));
Route::get('/register', fn() => view('auth.register'));


Route::get('/paiement', fn() => view('paiement'));

// Broadcast:: routes();

Route::get('/admin/dashboard', fn() => view('admin.dashboard'));
Route::get('/driver/dashboard', fn() => view('driver.dashboard'));
