<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\KomponenTable;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/load-luckysheet/{sheetId}', [KomponenTable::class, 'loadLuckysheet']);

Route::post('/save-luckysheet', [KomponenTable::class, 'saveLuckysheet']);
