{{--
    Phase 2.2 的接線驗證頁（Phase 2.3 元件轉好後，這頁換成元件目錄）。

    驗三條線：
      1. Vite 進來的 Pi DS SCSS 有生效（.gl_* / .fz-* 有樣式）
      2. /fonts 與 /assets 的 public symlink 通（icon 不是空白方框）
      3. blade 在容器內 render 得出來、套件 symlink 讀得到

    本頁只使用經 grep 確認存在的 class：
      .gl_btn / .gl_btn-md / .gl_btn-basic / .gl_btn-info  （components/_button.scss）
      .fz-tit / .fz-title-lg                               （tokens/_typography.scss）
      .icon .icon-search / .icon-download / .icon-chevron-right（assets/symicon.css）
--}}
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pi DS preview — 接線驗證</title>

    @vite('resources/scss/app.scss')

    {{-- symicon 的 icon-* class 清單，不走 Vite（url() 是 /fonts 絕對路徑） --}}
    <link rel="stylesheet" href="/assets/symicon.css">
</head>
<body>
    <main style="max-width: 720px; margin: 48px auto; padding: 0 24px;">
        <h1 class="fz-headline-sm fz-tit">Pi DS preview 接線驗證</h1>

        <p class="fz-body-md">
            這頁還沒有用 <code>&lt;x-pi::*&gt;</code> 元件 —— 元件在 Phase 2.3 才建立。
            這裡只確認 SCSS / 字型 / blade render 三條線都通。
        </p>

        <h2 class="fz-title-lg fz-tit">1. SCSS（<code>@layer pi</code> 內的 class）</h2>
        <p>
            <button type="button" class="gl_btn gl_btn-md gl_btn-basic">basic 按鈕</button>
            <button type="button" class="gl_btn gl_btn-md gl_btn-info">info 按鈕</button>
        </p>

        <h2 class="fz-title-lg fz-tit">2. Icon 字型（<code>/assets</code> + <code>/fonts</code> symlink）</h2>
        <p class="fz-body-md">下面三個應該是圖示而非空白方框：
            <i class="icon icon-search"></i>
            <i class="icon icon-download"></i>
            <i class="icon icon-chevron-right"></i>
        </p>

        <h2 class="fz-title-lg fz-tit">3. 環境</h2>
        <ul class="fz-body-md">
            <li>Laravel {{ app()->version() }}</li>
            <li>套件版本：{{ \Composer\InstalledVersions::getPrettyVersion('company/pi-design-system') }}</li>
            <li>套件實體路徑：<code>{{ realpath(base_path('vendor/company/pi-design-system')) }}</code></li>
        </ul>
    </main>
</body>
</html>
