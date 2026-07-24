# Blade prototype 落地：目錄、layout、route、跑法（Laravel + Vite）

> 以下路徑為 Laravel 標準慣例的**假設**。新專案結構不同時，改這裡 + 對應調整，並同步更新 SKILL.md 前置假設段。

## 一次性前置（新專案設定，只做一次）

> **懶人法**：直接跑 skill 內附 `setup.sh`，自動做完 1–4 + 建 layout scaffold + 印剩餘手動步驟：
> ```bash
> bash .claude/skills/pm-to-blade/setup.sh <Pi-Design-System repo 路徑>
> ```
> 以下為它做的事（也可手動）：

1. **Vendor Pi DS**：把 Pi DS 的 `src/` 複製到 `resources/sass/pi-ds/`（含 `tokens/` `base/` `components/` `index.scss`）。字型只有 **symicon**（`symicon-6.4s.woff*`）是本地檔（Google Sans Flex 內嵌 CDN、Noto Sans TC `@import` CDN），copy 到 `public/fonts/`，並在 app.scss 覆寫 `$font-path: '/fonts'`。
2. **編譯入口** `resources/sass/app.scss`：
   ```scss
   @use 'pi-ds/index' as *;          // 全套 tokens + base + components
   // 各 prototype 組合件樣式：
   @use 'prototypes/signup';          // 對應 resources/sass/prototypes/_signup.scss
   ```
3. **`vite.config.js`** input 含 scss：
   ```js
   laravel({ input: ['resources/sass/app.scss', 'resources/js/app.js'], refresh: true })
   ```
   需 `npm i -D sass`。
4. **icon 字型**（用到才做）：`assets/symicon.css` + woff 放 `public/`，layout `<link>` 引入，或併進 app.scss。

## Prototype layout（骨架，放 `resources/views/prototypes/layout.blade.php`）

```blade
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prototype — @yield('title', 'Prototype')</title>
  @vite(['resources/sass/app.scss'])
</head>
<body>
  @yield('content')
</body>
</html>
```

## Prototype view（每個需求一支）

`resources/views/prototypes/<name>.blade.php`：

```blade
@extends('prototypes.layout')
@section('title', '會員註冊')
@section('content')
  {{-- 乾淨、可被前端直接抬走的頁面標記；只用 vendored gl_* class --}}
  <form class="..." action="#">
    <div class="gl_form-group">
      <input class="gl_form-control" type="email" placeholder="Email">
    </div>
    <button type="submit" class="gl_btn gl_btn-md gl_btn-success">註冊</button>
  </form>
@endsection
```

**原則**：產物是**實際可用的頁面標記**，不是展示卡片；前端會直接拿去接流程，所以別包 demo 殼、別留假資料以外的雜訊。

## 組合件 scss（門檻第 2 層才需）

`resources/sass/prototypes/_<name>.scss`，class prototype-scoped、值全用現有 token：

```scss
@use 'pi-ds/tokens' as *;
// 組合件：無正式元件，用現有 token 疊（⚠️ 待確認，非 DS 正式件）
.proto-signup-banner {
  padding: 14px 20px;
  border-radius: var(--radius-md);
  background: var(--cl-basic-900);
}
```
記得在 `app.scss` `@use 'prototypes/<name>';`。

## Route（讓 preview 可開）

`routes/web.php` —— 最簡單：明列，或白名單動態：

```php
// 明列（安全、清楚）
Route::view('/prototypes/signup', 'prototypes.signup');

// 或白名單動態（勿直接吃 request 值當 view 名，避免任意 view 載入）
Route::get('/prototypes/{name}', function (string $name) {
    abort_unless(view()->exists("prototypes.$name"), 404);
    return view("prototypes.$name");
})->where('name', '[a-z0-9-]+');
```

## 跑 + 驗證

- `npm run dev`（Vite HMR）+ `php artisan serve`。
- 開 `http://127.0.0.1:8000/prototypes/<name>` → 確認頁面 render、瀏覽器 console 無 Vite/Sass error、樣式套上（`gl_*` 生效）。

## vendored Pi DS 內部結構（查 token/class 用）

- 元件 `resources/sass/pi-ds/components/_<name>.scss`；token `resources/sass/pi-ds/tokens/_*.scss`；入口 `pi-ds/index.scss`（`@forward tokens/base/components`）。
- class 慣例：元件前綴 `gl_`（`gl_btn gl_btn-md`、`gl_border-inner`）；字級 `fz-*`；token 以 CSS var 用（`var(--cl-basic-900)`、`var(--radius-md)`）。
- 禁自創 —— 見 [token-rules.md](token-rules.md)。
