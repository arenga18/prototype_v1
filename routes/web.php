<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\KomponenTable;
use App\Livewire\KomponenModul;

use App\Livewire\ModulModal;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/load-luckysheet/{sheetId}', [KomponenTable::class, 'loadLuckysheet']);

Route::post('/save-luckysheet', [KomponenTable::class, 'saveLuckysheet']);

Route::get('modulmodal', ModulModal::class);

Route::post('/save-spreadsheet', [KomponenModul::class, 'save']);
Route::post('/update-spreadsheet', [KomponenModul::class, 'update']);

Route::get('/get-modul-data', [KomponenModul::class, 'getModulData']);
