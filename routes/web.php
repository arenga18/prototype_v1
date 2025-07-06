<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\KomponenTable;
use App\Livewire\KomponenModul;
use App\Livewire\PartComponentLivewire;
use App\Livewire\RemovablePartLivewire;
use App\Http\Controllers\ModelDataController;
use App\Http\Controllers\CabinetController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/save-spreadsheet', [KomponenModul::class, 'save']);
Route::post('/update-spreadsheet', [KomponenModul::class, 'update']);

Route::post('/update-project', [KomponenTable::class, 'update']);

Route::post('/save-part', [PartComponentLivewire::class, 'save']);
Route::post('/update-part', [PartComponentLivewire::class, 'update']);

Route::post('/save-removable-part', [RemovablePartLivewire::class, 'save']);
Route::post('/update-removable-part', [RemovablePartLivewire::class, 'update']);

Route::get('/model-data/{model}', [ModelDataController::class, 'getModelData']);
Route::get('/modul-by-cabinet', [ModelDataController::class, 'getModulByCabinet']);
Route::put('/update-modul', [ModelDataController::class, 'updateModul']);
