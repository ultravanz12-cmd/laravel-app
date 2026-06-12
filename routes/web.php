<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SheetController;

Route::get('/', [SheetController::class, 'index'])->name('sheet.index');

Route::post('/upload', [SheetController::class, 'upload'])->name('sheet.upload');

Route::get('/sheet/edit/{id}', [SheetController::class, 'edit'])->name('sheet.edit');

Route::post('/sheet/save/{id}', [SheetController::class, 'save'])->name('sheet.save');

Route::get('/sheet/download/{id}', [SheetController::class, 'download'])->name('sheet.download');