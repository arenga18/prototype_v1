<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\KomponenTable;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/test-livewire', KomponenTable::class);
