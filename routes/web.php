<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\KomponenTable;
use App\Livewire\KomponenModul;
use App\Livewire\PartComponentLivewire;
use App\Livewire\RemovablePartLivewire;
use App\Http\Controllers\ModelDataController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/save-spreadsheet', [KomponenModul::class, 'save']);
Route::post('/update-spreadsheet', [KomponenModul::class, 'update']);
Route::get('/get-modul-data', [KomponenModul::class, 'getModulData']);

Route::post('/update-project', [KomponenTable::class, 'update']);

Route::post('/save-part', [PartComponentLivewire::class, 'save']);
Route::post('/update-part', [PartComponentLivewire::class, 'update']);

Route::post('/save-removable-part', [RemovablePartLivewire::class, 'save']);
Route::post('/update-removable-part', [RemovablePartLivewire::class, 'update']);

Route::get('/model-data/{model}', [ModelDataController::class, 'getModelData']);
Route::get('/modul-by-cabinet', [ModelDataController::class, 'getModulByCabinet']);
Route::put('/update-modul', [ModelDataController::class, 'updateModul']);

Route::get('/get-modul', [KomponenTable::class,  'loadUpdatedGroupedComponents']);

Route::prefix('admin/projects/{project}/reports')->controller(ReportController::class)->group(function () {
    // Route untuk menyimpan data
    Route::post('store-data', 'storeReportData')->name('reports.store-data');
    Route::get('{reportType}', 'showReport')->name('reports.show');

    // Redirect routes for specific report types
    Route::get('full-recap', function ($project) {
        return redirect()->route('reports.show', ['project' => $project, 'reportType' => 'full-recap']);
    });
    Route::get('KS', function ($project) {
        return redirect()->route('reports.show', ['project' => $project, 'reportType' => 'KS']);
    });
    Route::get('nonKS', function ($project) {
        return redirect()->route('reports.show', ['project' => $project, 'reportType' => 'nonKS']);
    });
});
