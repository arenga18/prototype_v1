<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\KomponenTable;
use App\Livewire\KomponenModul;
use App\Livewire\PartComponentLivewire;


Route::get('/', function () {
    return view('welcome');
});

Route::post('/save-spreadsheet', [KomponenModul::class, 'save']);
Route::post('/update-spreadsheet', [KomponenModul::class, 'update']);

Route::post('/update-project', [KomponenTable::class, 'update']);

Route::post('/save-part', [PartComponentLivewire::class, 'save']);
Route::post('/update-part', [PartComponentLivewire::class, 'update']);

Route::get('/get-modul-data', [KomponenModul::class, 'getModulData']);
