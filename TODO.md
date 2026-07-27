# TODO — Design Guideline 平台化（Phase 1 + 2）

> 依 `design-guideline-spec.md` 執行，含針對本 repo 現況與公司 Docker 環境的偏離修正。
> 進度勾選：`[ ]` 未開始 · `[x]` 完成

---

## 決策與偏離（已確認）

| 項目 | spec 原文 | 本次執行 | 理由 |
|---|---|---|---|
| SCSS 目錄 | `resources/scss` | **照 spec 搬** | Composer 套件慣例 `src/` = PSR-4 PHP，SCSS 屬 `resources/` |
| class 前綴 | `dg-` | **保留 `gl_`** | `gl_` 已是命名空間；解 specificity 的是 `@layer` 不是前綴 |
| CSS 變數 | `--dg-` | **保留 `--cl-*` / 語意 alias** | 同上 |
| layer 名稱 | `@layer dg` | **`@layer pi`** | 對齊 Pi DS 命名 |
| Blade 命名空間 | `<x-dg::*>` | **`<x-pi::*>`** | 同上 |
| reset | 拆出 + 元件全面自給自足 | **拆出；元件只補 2 項契約** | 各專案都有 reset，通用部分不必重複；但 `corner-shape` / `font-weight: 500` 沒有任何標準 reset 提供 |
| box-sizing | — | **`reset.scss` 補全域 `border-box`** | 專案 reset 幾乎都有；補上讓 preview 對齊專案現實 |
| 套件名 | `company/design-guideline` | **`company/pi-design-system`（placeholder）** | 等公司 GitHub org 名 |
| 排除討論區 | `archive.exclude` | **`.gitattributes` `export-ignore`** | Composer 從 VCS 抓 GitHub zipball（`git archive` 產生），只認 export-ignore |
| 現有 preview | — | **`preview/` → `preview-static/`**，Laravel app 進 `preview/` | 終態對齊 spec；過渡期兩套並存 |
| 容器 | 未提 | **`preview/` 內輕量 docker-compose（單一 php-cli container），不用 Laradock** | 本 repo 是 Composer library，出貨 SCSS 原始碼 + blade，無 build step、無 DB、無 queue。專案端只 `composer require`，永不執行本 repo。Laradock 那套 nginx/mysql/redis 全都用不到 |

### 元件契約 vs reset 的切分（本次核心設計）

**`resources/scss/reset.scss`（頁面級意見，專案不載，preview 載）**
- `*` 全域 `box-sizing: border-box`
- `html, body` font-family / color / background / font-smoothing
- `h1..h6` / `p` / `small` / `code`

**`resources/scss/_component-base.scss`（元件契約，跟著套件出貨）**
- `corner-shape: superellipse(1.05)` —— 新 CSS 屬性，2024 才進規格，無任何標準 reset 提供。元件內 20 處 `border-radius` 全靠它
- `font-weight: 500` —— normalize.css / Tailwind preflight / Bootstrap reboot 一律給 400 或 inherit。16 支元件只有 1 處自己宣告

用聚合 attribute selector 一條規則覆蓋（而非灑進 20 處），因為半數 `border-radius` 的擁有者不是 `gl_` 前綴（`input[type=checkbox]`、`&::before`、`.layer`、`.knobs`、`.form-feedback`、`.iw_pagination-outer-v3`）。

### 容器邊界（本次新增決策）

```
Pi-Design-System/          ← 套件本體：無 framework、無 build、無 DB、不進容器
└── preview/               ← 唯一需要 PHP 的地方（render blade），dev tool，容器只包這層
```

- **PHP 在 container**（統一版本、附帶 composer；host 目前 `which composer` → not found）
- **Vite 在 host**（`node_modules/` 已在 host，省一個 node container；hot file 走 bind mount 共享，瀏覽器直連 host 的 5173）
- **掛載整個 repo root，不是只掛 `preview/`** —— composer path repository 的 symlink（`preview/vendor/company/pi-design-system` → `../../`）指回上層，只掛 `preview/` 會斷鏈

真正需要 Docker 的時機（不是現在）：Phase 4 CI 跑 visual regression（需 PHP + headless browser image）、`scripts/fetch-host.php` 要連內網 staging。

### spec 未涵蓋、本次補上
- `fonts/` + `assets/symicon.css` 納入套件出貨清單（元件吃 `icon-*` class，漏了 icon 全空）
- `@extend .fz-tit` → mixin（**實測** `@extend` 會讓樣式逃出 `@layer`，破壞 D5 保證）
- `resources/scss/tokens/` 實際輸出 `:root {}`（7 支檔）與 `.fz-*` class，不是 spec 3.4 說的「零 CSS 輸出」→ 文件要更正
- 無前綴 class 清單（撞名風險）：`.fz-*`、`.text-*`、`.form-feedback`、`.form-prompt-text`、`.is-invalid`、`.is-valid`、`.iw_pagination-outer-v3`

### 待使用者提供
- [ ] **公司 GitHub org 名稱 + repo 名** → composer 套件名與 `repositories.pi` URL（Phase 1.6 前）
- [x] ~~**Laradock 路徑 + `APP_CODE_PATH_HOST`**~~ → 已不需要，改用 `preview/` 自帶 compose
- [ ] **8000 / 5173 port 是否與現有 Laradock 服務衝突** → 衝突就改 mapping（Phase 2.2 前，`docker ps` 可自查）

### 已知風險
- `.is-invalid` / `.is-valid` 與 Bootstrap 完全撞名（`@layer pi` 解 specificity，語意仍衝突）→ Phase 3 再議
- reset 加全域 `border-box` 會改變現有 preview 排版（content-box → border-box），有 padding + 固定尺寸處會變小 → **1.5 需目視比對，跑版就報告**
- Phase 4 Composer 私有套件在**專案端容器**（各專案的 Laradock）內認證：建議 GitHub token + HTTPS（`auth.json` gitignore 或 `COMPOSER_AUTH` env），不要 SSH key（container 常重建要重掛）。本 repo 的 `preview/` 走 path repository symlink，不碰網路、不需認證
- Phase 4 專案端 `@use` 路徑：Sass 不吃 Vite alias，只吃 `loadPaths`。建議專案端放 shim `resources/sass/_pi-ds.scss` → `@forward '../../vendor/company/pi-design-system/resources/scss/index';`，既有 `@use 'pi-ds' as *` 幾乎不用改

---

## Phase 1：套件化與 SCSS 分層（不需 docker，host node 即可）

### 1.1 `@extend` → mixin（**必須先做，@layer 的前置條件**）
- [x] `tokens/_typography.scss` 加 `@mixin fz-tit`（保留 `.fz-tit` class 供 HTML 直接用）
- [x] `components/_content-switcher.scss:41` `@extend` → `@include`
- [x] `components/_pagination.scss:49` 同上
- [x] `components/_dropdown.scss:8` 同上
- [x] `base/_reset.scss:23` 同上
- [x] `npm run build` → 確認 CSS 無 `@extend` 產生的選擇器合併洩漏

### 1.2 目錄搬遷 `src/` → `resources/scss/`
- [x] `git mv` 保留 history（`src/` 之後留給 PSR-4 PHP）
- [x] `base/_fonts.scss` 的 `$font-path` 預設 `"../../fonts"` → `"../../../fonts"`
- [x] `package.json` script 路徑（build / build:min / build:tokens / watch / lint:scss / test）
- [x] `vite.config.js` 註解與路徑
- [x] `preview/*.html`（20 支）`<link href="/src/index.scss">` → 新路徑
- [x] `scripts/check-build.mjs`
- [x] 文件：`README.md`、`STRUCTURE.md`、`CLAUDE.md`、`SKILL.md`、`docs/ai-guide.md`、`docs/prototype-README.template.md`、`docs/prototype-CLAUDE.template.md`
- [x] `.scratch/pm-to-blade-skill/`（`setup.sh`、`vendor-copy.sh`、`references/blade-setup.md`）
- [x] `.claude/skills/figma-to-pi-ds/SKILL.md`、`.claude/skills/pm-to-preview/SKILL.md`

### 1.3 `@layer pi` 包裝
- [x] `components/*.scss` 16 支各自包 `@layer pi { }`
- [x] `base/_utilities.scss`（`.text-*`）包 `@layer pi { }`
- [x] `tokens/_typography.scss` 的 `.fz-*` class 包 `@layer pi { }`
- [x] `tokens/` 的 `:root {}` 進 `@layer pi`（讓專案未分層的 `:root` 覆寫必勝，不用管 import 順序）
- [x] `npm run build` → 目視 preview 確認零視覺差異

### 1.4 reset 拆出
- [x] 新增 `resources/scss/reset.scss`（頂層獨立檔，內容自 `base/_reset.scss` 搬移 + 補全域 `box-sizing: border-box`）
- [x] `resources/scss/index.scss`：`@forward "base"` → `@forward "base/fonts"` + `@forward "base/utilities"`
- [x] 刪 `base/_reset.scss` 與 `base/index.scss` 的 `@forward "reset"`
- [x] 新增 `resources/scss/preview-all.scss`（`reset` + `index`），供 `preview/*.html` 維持視覺
- [x] `preview/*.html`（20 支）改吃 `preview-all.scss`
- [x] **目視比對**：border-box 改動是否讓現有 preview 跑版；有則報告並微調

### 1.5 元件契約
- [x] 新增 `resources/scss/_component-base.scss`（`corner-shape` + `font-weight: 500` 聚合規則，包 `@layer pi`）
- [x] `index.scss` 在 components 之前 `@forward "component-base"`
- [x] **不載 reset 的驗證頁**：`preview/_no-reset.html` 只載 `index.scss`，逐元件與載 reset 版對照，確認圓角形狀與字重一致

### 1.6 Composer 套件本體
- [x] `composer.json`（`company/pi-design-system`、PSR-4 `Company\PiDesignSystem\` → `src/`、依賴僅 `illuminate/support`、laravel provider extra）
- [x] `src/PiDesignSystemServiceProvider.php`
  - `loadViewsFrom(resources/views, 'pi')`
  - `Blade::anonymousComponentPath(resources/views/components, 'pi')`
  - `Blade::directive('piFragment', ...)` → render 空字串（Phase 3 才消費 manifest）
- [x] `.gitattributes` 加 `export-ignore`：`/prototypes`、`/preview`、`/preview-static`、`/scripts`、`/.scratch`、`/docs`、`/dist*`、`/node_modules`
- [x] 確認出貨含 `fonts/` 與 `assets/symicon.css`（不可 export-ignore）
- [ ] `composer validate` —— **待辦**：host 未安裝 composer（`which composer` → not found）

### 1.7 Phase 1 驗收
- [x] `npm run dev` → 20 支 preview 頁視覺與改動前一致（差異需可解釋）
- [x] `npm run test`（build smoke，新增 `@layer pi` 檢查項）
- [x] `dist/pi-ds.css` 內所有 `.gl_*` / `.fz-*` / `.text-*` 都在 `@layer pi` 內，無漏網
- [x] `git commit`

---

## Phase 2：Blade 元件與 preview Laravel app

### 2.1 現有 preview 讓位
- [ ] `git mv preview preview-static`
- [ ] `vite.config.js` input、`package.json`、文件同步
- [ ] `.gitattributes` 的 export-ignore 確認已含 `/preview` 與 `/preview-static`（1.6 已加，複查）

### 2.2 容器與 Laravel app
- [ ] `preview/Dockerfile`
  - `FROM php:8.2-cli`
  - `apt-get install git unzip libonig-dev libzip-dev` → `docker-php-ext-install mbstring zip`（官方 php image 未內建 mbstring；composer 需 zip/unzip）
  - `COPY --from=composer:2 /usr/bin/composer /usr/bin/composer`
- [ ] `preview/docker-compose.yml`
  - `volumes: ../:/var/www` —— **掛 repo root，不是只掛 `preview/`**（symlink 斷鏈的成因）
  - `working_dir: /var/www/preview`
  - `ports: "8000:8000"`
  - `command: php artisan serve --host=0.0.0.0 --port=8000`
- [ ] `docker compose run --rm app composer create-project laravel/laravel:^12.0 .`（容器內建 Laravel app）
- [ ] `preview/composer.json` 加 path repository `../` + `symlink: true` → `require company/pi-design-system: "*"`
- [ ] `docker compose run --rm app composer update` → 確認 `preview/vendor/company/pi-design-system` 是 symlink（`ls -l`）
- [ ] `preview/.gitignore` 排除 `vendor/`、`node_modules/`、`.env`、`public/hot`、`public/build`、`storage/`（Laravel 預設已多數含）
- [ ] `preview/vite.config.js`（**跑在 host，不在 container**）
  - `server.host: '127.0.0.1'`、port 5173
  - `server.watch.ignored` 排除 `vendor/company/pi-design-system`（symlink 指回 repo root，會遞迴掃）
  - `css.preprocessorOptions.scss.loadPaths` → `../resources/scss`
- [ ] fonts / `assets/symicon.css` 靜態資源：symlink 進 `preview/public/` 或 vite `publicDir`（二擇一，實作時決定）
- [ ] `preview/README.md`：起動兩步（`docker compose up -d` + host `npm run dev`）、port、常見問題
- [ ] 驗證：`http://localhost:8000` render 出 blade；改根目錄 SCSS → 瀏覽器即時反映（symlink + HMR 都通）

### 2.3 前 3 支元件轉 Blade（驗證模式）
- [ ] `resources/views/components/button.blade.php` + `button.meta.php`
- [ ] `resources/views/components/form-control.blade.php` + meta
- [ ] `resources/views/components/callout.blade.php` + meta
- [ ] `resources/views/layouts/preview.blade.php`
- [ ] 驗證 `<x-pi::button>` 解析、`@layer pi` 在 Laravel preview 環境與 `preview-static/` 行為一致

### 2.4 批次轉剩餘元件
- [ ] checkbox / radio / toggle
- [ ] alert / notification / loading
- [ ] content-switcher / dropdown / pagination
- [ ] modal
- [ ] 各配 `.meta.php`

### 2.5 Preview 自動生成
- [ ] `PreviewIndexController`（掃 `resources/views/components/*.meta.php`）
- [ ] `ComponentPreviewController`（render meta 的 examples）
- [ ] `routes/web.php`
- [ ] 驗證新增元件不用手改 preview 頁

### 2.6 `scripts/sync-component-list.php`
- [ ] meta → `CLAUDE.md` 的 `<!-- COMPONENTS:START/END -->` 區段
- [ ] `CLAUDE.md` 加硬性規則段（spec 5.6，layer/前綴改成 `pi` / `gl_`）
- [ ] 執行一次並確認清單與實際元件一致

### 2.7 Phase 2 驗收
- [ ] 16 支元件全可在 `preview/` Laravel app 檢視
- [ ] `preview-static/` 可廢除的判定（markup 是否已由 blade + meta 完全覆蓋）
- [ ] `CHANGELOG.md` 記錄
- [ ] `git commit`

---

## Review

（實作完成後填寫：變更摘要、遇到的問題與解法、Phase 3 待辦）
