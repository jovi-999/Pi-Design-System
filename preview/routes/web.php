<?php

use App\Http\Controllers\ComponentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pi DS preview 路由
|--------------------------------------------------------------------------
|
| 元件目錄：由 meta 檔自動生成，新增元件不需要動這裡。
| Prototype 路由在 Phase 3 補上（/preview/{project}/{name}）。
|
*/

Route::redirect('/', '/components');

Route::get('/components', [ComponentController::class, 'index'])->name('components.index');
Route::get('/components/{slug}', [ComponentController::class, 'show'])->name('components.show');

// Phase 2.2 的接線驗證頁（SCSS / 字型 / blade render 三條線）
Route::view('/_smoke', 'welcome')->name('smoke');
