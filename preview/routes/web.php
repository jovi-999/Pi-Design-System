<?php

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pi DS preview 路由
|--------------------------------------------------------------------------
|
| 元件目錄與 prototype 清單都由檔案掃描生成，新增內容不需要動這裡。
|
| 路徑用 /prototypes 而非 spec 寫的 /preview —— 整個 app 本身就叫 preview，
| /preview/xxx 是多餘的一層。
|
*/

Route::redirect('/', '/prototypes');

Route::get('/components', [ComponentController::class, 'index'])->name('components.index');
Route::get('/components/{slug}', [ComponentController::class, 'show'])->name('components.show');

Route::get('/prototypes', [PrototypeController::class, 'index'])->name('prototypes.index');
Route::get('/prototypes/{project}/{name}', [PrototypeController::class, 'show'])->name('prototypes.show');

// Phase 2.2 的接線驗證頁（SCSS / 字型 / blade render 三條線）
Route::view('/_smoke', 'welcome')->name('smoke');
