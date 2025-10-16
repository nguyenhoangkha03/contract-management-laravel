<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractExportController;
use App\Http\Controllers\ContractWordExportController;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin');
});

Route::get('/contracts/{contract}/export-word', [ContractController::class, 'exportWord'])
    ->name('contract.exportWord');

Route::get('/export-excel', [ContractExportController::class, 'exportExcel']);

Route::get('/export-word/{id}', [ContractWordExportController::class, 'exportWord']);
