# Blade prototype 落地：一頁一支 scss（Laravel + Vite + Laradock）

> 對齊本專案慣例：**一頁一支 scss、列在 `vite.config.*` 的 `input`、不用共用 app.scss**。
> 路徑以此為準；結構不同時改本檔 + SKILL.md 前置假設段。

## 一次性前置（新專案設定，只做一次）

1. **Vendor Pi DS**：Pi DS `src/` → `resources/sass/pi-ds/`（用 host 端 `vendor-copy.sh` 自動做）。
2. **字型**：只有 symicon 是本地檔（`symicon-6.4s.woff*` → `public/fonts/`）；Google Sans Flex 內嵌 CDN、Noto Sans TC `@import` CDN。若某支 preview scss 引到 symicon 且 `$font-path` 需覆寫，在該 scss 頂端：
   ```scss
   @use 'pi-ds/base/fonts' with ($font-path: '/fonts');
   @use 'pi-ds/index' as *;
   ```
   （多數情況 `@use 'pi-ds/index'` 即可；字型路徑對就不必覆寫）
3. **Sass 解析**：確認 `vite.config.*` 的 `css.preprocessorOptions.{scss,sass}.loadPaths` 含 `'resources/sass'` → `@use 'pi-ds/index'` 直接解得到。無則用相對路徑。
4. **`resources/views/prototypes/` 目錄**存在（`setup.sh` 會建）。
- `setup.sh`（容器內跑）只做「建 prototypes 目錄 + 印慣例提示」，**不碰 app.scss、不裝 sass**（本專案已具備）。

## 每個 prototype（PM 指定名 `preview-<name>`，三件事）

### ① SCSS 入口 `resources/sass/preview-<name>.scss`

```scss
@use 'pi-ds/index' as *;   // tokens + base + components
// 組合件（門檻第 2 層）：prototype-scoped class，值全用現有 token
.preview-<name>-banner {
  padding: 14px 20px;
  border-radius: var(--radius-md);
  background: var(--cl-basic-900);
}
```

### ② Blade view `resources/views/prototypes/preview-<name>.blade.php`

```blade
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prototype — <name></title>
  {{-- 用 icon 才需： <link rel="stylesheet" href="{{ asset('symicon.css') }}"> --}}
  @vite(['resources/sass/preview-<name>.scss'])
</head>
<body>
  {{-- 乾淨、可被前端直接抬走的頁面標記；只用 vendored gl_* class --}}
</body>
</html>
```

### ③ 註冊到 vite input + route

`vite.config.*` 的 `input` 陣列加一行（跟著既有一頁一支慣例）：
```js
input: [
  // …既有 entries…
  'resources/sass/preview-<name>.scss',
],
```

`routes/web.php` 加（白名單式，勿直接吃 request 當 view 名）：
```php
Route::get('/prototypes/{name}', function (string $name) {
    abort_unless(view()->exists("prototypes.$name"), 404);
    return view("prototypes.$name");
})->where('name', 'preview-[a-z0-9-]+');
```

## 跑 + 驗證（Laradock）

```bash
cd laradock
docker-compose exec workspace bash        # 進容器
# 容器內專案根：
npm run watch                             # = vite（改 input 後需重啟才吃新 entry）
```
另一終端開瀏覽器（走 laradock nginx 對外 host/port，非 artisan serve）：
`http://<laradock-host>/prototypes/preview-<name>` → 確認 render、console 無 Vite/Sass error、`gl_*` 生效。

> 改了 `vite.config.*` 的 input 要**重啟 `npm run watch`** 才會編譯新入口。

## vendored Pi DS 內部結構（查 token/class 用）

- 元件 `resources/sass/pi-ds/components/_<name>.scss`；token `resources/sass/pi-ds/tokens/_*.scss`；入口 `pi-ds/index.scss`（`@forward tokens/base/components`）。
- class 慣例：元件前綴 `gl_`（`gl_btn gl_btn-md`、`gl_border-inner`）；字級 `fz-*`；token 以 CSS var 用（`var(--cl-basic-900)`、`var(--radius-md)`）。
- 禁自創 —— 見 [token-rules.md](token-rules.md)。
