<?php

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\FoundationController;
use App\Http\Controllers\PrototypeController;
use App\Support\ComponentCatalog;
use App\Support\IconCatalog;
use App\Support\PrototypeCatalog;
use App\Support\TokenCatalog;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pi DS preview 路由
|--------------------------------------------------------------------------
|
| 三區：foundation / components / prototypes。三邊的清單都由檔案掃描生成，
| 新增內容不需要動這裡。
|
| 路徑用 /prototypes 而非 spec 寫的 /preview —— 整個 app 本身就叫 preview，
| /preview/xxx 是多餘的一層。
|
*/

Route::get('/', fn () => view('home', [
    'tokenCount' => TokenCatalog::count(),
    'iconCount' => count(IconCatalog::all()),
    'componentCount' => ComponentCatalog::all()->count(),
    'prototypeCount' => PrototypeCatalog::all()->count(),
]))->name('home');

// ---------- Foundation ----------
// /foundation/tokens 要在 /foundation/{group} 之前 —— 否則 tokens 會被
// 當成 group 名稱吃掉，然後 404。
Route::get('/foundation', [FoundationController::class, 'index'])->name('foundation.index');
Route::get('/foundation/tokens', [FoundationController::class, 'tokens'])->name('foundation.tokens');
Route::get('/foundation/icons', [FoundationController::class, 'icons'])->name('foundation.icons');
Route::get('/foundation/{group}', [FoundationController::class, 'group'])->name('foundation.group');

// ---------- 元件 ----------
Route::get('/components', [ComponentController::class, 'index'])->name('components.index');
Route::get('/components/{slug}', [ComponentController::class, 'show'])->name('components.show');

// ---------- Prototype ----------
Route::get('/prototypes', [PrototypeController::class, 'index'])->name('prototypes.index');
Route::get('/prototypes/{project}/{name}', [PrototypeController::class, 'show'])->name('prototypes.show');

// Phase 2.2 的接線驗證頁（SCSS / 字型 / blade render 三條線）
Route::view('/_smoke', 'welcome')->name('smoke');
