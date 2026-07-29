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
| 套件名 | `company/design-guideline` | **`pi-tw/pi-design-system`** | `pi-tw` = 公司 GitHub org（Symmetry Information Co., Ltd.）。PSR-4 namespace `PiTw\PiDesignSystem\` 對齊 vendor |
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
- **掛載整個 repo root，不是只掛 `preview/`** —— composer path repository 的 symlink（`preview/vendor/pi-tw/pi-design-system` → `../../`）指回上層，只掛 `preview/` 會斷鏈

真正需要 Docker 的時機（不是現在）：Phase 4 CI 跑 visual regression（需 PHP + headless browser image）、`scripts/fetch-host.php` 要連內網 staging。

### spec 未涵蓋、本次補上
- `fonts/` + `assets/symicon.css` 納入套件出貨清單（元件吃 `icon-*` class，漏了 icon 全空）
- `@extend .fz-tit` → mixin（**實測** `@extend` 會讓樣式逃出 `@layer`，破壞 D5 保證）
- `resources/scss/tokens/` 實際輸出 `:root {}`（7 支檔）與 `.fz-*` class，不是 spec 3.4 說的「零 CSS 輸出」→ 文件要更正
- 無前綴 class 清單（撞名風險）：`.fz-*`、`.text-*`、`.form-feedback`、`.form-prompt-text`、`.is-invalid`、`.is-valid`、`.iw_pagination-outer-v3`

### 待使用者提供
- [x] ~~**公司 GitHub org 名稱**~~ → `pi-tw`（Symmetry Information Co., Ltd.）。套件名已定為 `pi-tw/pi-design-system`、namespace `PiTw\PiDesignSystem\`
- [x] **repo 推到 `pi-tw` org** —— 目前在個人帳號 `jovi-999/Pi-Design-System-`（結尾多一個 hyphen，是打錯）。使用者要在本機實測沒問題後才推。
      推上去之後只有一件事要改：各專案執行的 `composer config repositories.pi vcs git@github.com:pi-tw/<repo>.git`。
      **套件名與 namespace 不需要再動** —— composer 的 vendor 名與 repo 放在哪無關，所以現在先定名，之後搬家零成本。
- [x] ~~**Laradock 路徑 + `APP_CODE_PATH_HOST`**~~ → 已不需要，改用 `preview/` 自帶 compose
- [x] ~~**8000 / 5173 port 衝突確認**~~ → 已查：8000 被 `stock-tssco-quote-web` 佔用 → preview 改用 **8100**；5173 free

### 已知風險
- `.is-invalid` / `.is-valid` 與 Bootstrap 完全撞名（`@layer pi` 解 specificity，語意仍衝突）→ Phase 3 再議
- reset 加全域 `border-box` 會改變現有 preview 排版（content-box → border-box），有 padding + 固定尺寸處會變小 → **1.5 需目視比對，跑版就報告**
- Phase 4 Composer 私有套件在**專案端容器**（各專案的 Laradock）內認證：建議 GitHub token + HTTPS（`auth.json` gitignore 或 `COMPOSER_AUTH` env），不要 SSH key（container 常重建要重掛）。本 repo 的 `preview/` 走 path repository symlink，不碰網路、不需認證
- Phase 4 專案端 `@use` 路徑：Sass 不吃 Vite alias，只吃 `loadPaths`。建議專案端放 shim `resources/sass/_pi-ds.scss` → `@forward '../../vendor/pi-tw/pi-design-system/resources/scss/index';`，既有 `@use 'pi-ds' as *` 幾乎不用改

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
- [x] `composer.json`（`pi-tw/pi-design-system`、PSR-4 `PiTw\PiDesignSystem\` → `src/`、依賴僅 `illuminate/support`、laravel provider extra）
- [x] `src/PiDesignSystemServiceProvider.php`
  - `loadViewsFrom(resources/views, 'pi')`
  - `Blade::anonymousComponentPath(resources/views/components, 'pi')`
  - `Blade::directive('piFragment', ...)` → render 空字串（Phase 3 才消費 manifest）
- [x] `.gitattributes` 加 `export-ignore`：`/prototypes`、`/preview`、`/preview-static`、`/scripts`、`/.scratch`、`/docs`、`/dist*`、`/node_modules`
- [x] 確認出貨含 `fonts/` 與 `assets/symicon.css`（不可 export-ignore）
- [x] `composer validate` —— 通過（`./composer.json is valid`）。host 沒裝 composer，走容器：
      `cd preview && docker compose run --rm app sh -lc 'cd /var/www && composer validate --no-check-publish'`
      （`--no-check-publish` 因為這是私有套件，不上 Packagist）

### 1.7 Phase 1 驗收
- [x] `npm run dev` → 20 支 preview 頁視覺與改動前一致（差異需可解釋）
- [x] `npm run test`（build smoke，新增 `@layer pi` 檢查項）
- [x] `dist/pi-ds.css` 內所有 `.gl_*` / `.fz-*` / `.text-*` 都在 `@layer pi` 內，無漏網
- [x] `git commit`

---

## Phase 2：Blade 元件與 preview Laravel app

### 2.1 現有 preview 讓位
- [x] `git mv preview preview-static`（21 檔，history 保留）
- [x] `vite.config.js`（`server.open` + `rollupOptions.input` 16 支）
- [x] `preview-static/*.html`：`/preview/preview.scss` link ×20、`index.html` 的 iframe `src`
- [x] 文件同步：README / STRUCTURE / SKILL / docs/ai-guide / .claude/skills / .scratch
      （`CHANGELOG.md` 舊條目與 `design-guideline-spec.md` 刻意不動 —— 歷史記錄 / spec 原文）
- [x] `.gitattributes` 的 export-ignore 確認已含 `/preview` 與 `/preview-static`（1.6 已加）
- [x] `.gitignore` 的 `preview/_shot_*.png` → `preview-static/_shot_*.png`
      （**rename 讓 pattern 失效，7 張 debug 截圖一度被誤 commit，已 `git rm --cached`**）
- [x] `npm run preview:build` 通過

### 2.2 容器與 Laravel app
- [x] `preview/Dockerfile`（`php:8.2-cli` + `docker-php-ext-install mbstring zip` + composer:2；`.dockerignore` 只留 Dockerfile，build context 最小）
- [x] `preview/docker-compose.yml`
  - `volumes: ../:/var/www` —— **掛 repo root，不是只掛 `preview/`**（symlink 斷鏈的成因）
  - `working_dir: /var/www/preview`
  - **port 8100 而非 8000** —— 8000 已被本機 `stock-tssco-quote-web` 佔用
- [x] 容器內建 Laravel 12.64.0（`create-project` 到 `/tmp/app` 再 `cp -a` 回來 —— 直接指向 `preview/` 會被「目錄非空」擋掉，docker 檔已在裡面）
- [x] `preview/composer.json` path repository `../` + `symlink: true`
- [x] `composer update` 通過；`vendor/pi-tw/pi-design-system -> ../../..`，ServiceProvider 已被 package discover
- [x] `preview/.gitignore` 補 `/database/*.sqlite*`（其餘 Laravel 預設已含）
- [x] `preview/vite.config.js`（跑在 host）
- [x] fonts / symicon：`public/fonts` → `../../fonts`、`public/assets` → `../../assets` symlink
- [x] `preview/README.md`（起動兩步、port、5 項設計取捨）
- [x] `STRUCTURE.md` 加 `preview/` 目錄說明
- [x] 驗證全通：`/` 200、`/assets/symicon.css` 200、`/fonts/*.woff2` 200、CSS 32 kB；
      `npm run dev` 寫的 `public/hot` 被容器內 PHP 讀到，頁面吐出 `127.0.0.1:5173` 的 dev URL

**實作中的三個偏離（與原計畫不同）**

1. **移除 Laravel 12 skeleton 的 Tailwind 4**（連帶 `resources/js/bootstrap.js` 的 axios）。
   Tailwind preflight 會跟 `reset.scss` / `@layer pi` 打架，而 preview 的唯一價值是
   忠實反映 Pi DS 本身 —— 引進 Tailwind 等於自己製造「preview 對、貼進專案走鐘」。
2. **SCSS 走實體相對路徑 `../../../resources/scss/preview-all`，不走 `loadPaths`、也不走 `vendor/` symlink。**
   `vendor/pi-tw/pi-design-system` 指回 repo 根，repo 根底下又有 `preview/`，Vite watcher
   走進去會無限遞迴；`server.watch.ignored` 排除 `**/vendor/**` 後，若 SCSS 還走 vendor 路徑
   就換成 HMR 不觸發。PHP 讀 blade 走 symlink 無此問題（不涉及 file watcher）。
3. **套件約束 `@dev` 而非 `*`** —— path repository 版本來自分支名（`dev-main`），撞
   `minimum-stability: stable`。專案端 Phase 4 走 VCS + tag，是 `^1.0`，不受影響。

### 2.3 前 3 支元件轉 Blade（驗證模式）
- [x] `button.blade.php` + meta（5 variant × 8 tone × 5 size + 純 icon）
- [x] `form-control.blade.php` + meta（整個 `.gl_form-group` 群組）
- [x] `callout.blade.php` + meta
- [x] `resources/views/layouts/preview.blade.php`
- [x] 驗證 `<x-pi::*>` 解析、輸出的 class 全部比對過 SCSS 實際存在
- [x] **prop 守衛**：不存在的組合丟例外而非輸出死 class（`.gl_btn-outline-dark`、`.gl_callout-orange` 都被擋下）

### 2.4 批次轉剩餘元件
- [x] checkbox / radio / toggle
- [x] alert / notification / loading
- [x] content-switcher / dropdown-item / pagination
- [x] modal
- [x] 各配 `.meta.php`
- [x] **`border` / `radius` / `shadow` 不轉元件** —— 是 utility class，套在別的元素上用，沒有自己的結構
- [x] 各元件 tone 清單不一致，守衛逐支照 SCSS 收斂（button 8 / callout·alert·notification 6 / toggle 5 / checkbox·radio 4 / content-switcher 3）

### 2.5 Preview 自動生成
- [x] `App\Support\ComponentCatalog`（掃 `vendor/` 內的 `*.meta.php`，與專案端同一條路徑）
- [x] `ComponentController`（目錄頁 + 單一元件頁，`Blade::render()` 跑 examples）
- [x] `gallery/` 三支 view（不放 `resources/views/components/`，那是匿名元件目錄）
- [x] `_chrome.scss`（preview 自己的版面樣式，前綴 `pv-`，不進 `@layer pi`）
- [x] 驗證新增元件不用手改 preview 頁

### 2.6 `scripts/sync-component-list.php`
- [x] meta → `CLAUDE.md` 的 `<!-- COMPONENTS:START/END -->` 區段
- [x] `--check` 模式（CI 用，不一致回 exit 1）
- [x] `notes` 中以 ⚠️ 開頭者抽成獨立的「已知元件缺口」段落
- [x] `CLAUDE.md` 加 prototype 六條硬性規則
- [x] 執行一次：13 支元件、2 個缺口

### 2.7 Phase 2 驗收
- [x] 13 支元件全可在 `preview/` Laravel app 檢視（**不是 16** —— border/radius/shadow 是 utility）
- [x] 142 個實際輸出的 class 全部存在於 `dist/pi-ds.css` + `symicon.css`
- [x] `CHANGELOG.md` 記錄
- [x] `git commit`
- [x] **`preview-static/` 已廢除**（見 Phase 3.9）

---

## Phase 3：Prototype 系統

### 3.1 Preview 路由與 controller
- [x] `PrototypeCatalog`（掃 `prototypes/`、靜態解析 manifest、數 page-scoped SCSS 行數）
- [x] `PrototypeController`：page 直接 render；fragment 注入 host 快照
- [x] 路由 `/prototypes`、`/prototypes/{project}/{name}`（**用 `/prototypes` 而非 spec 的 `/preview`** —— app 本身就叫 preview，多一層冗贅）
- [x] 注入失敗顯示裸片段 + 寫明原因，不靜默 fallback
- [ ] `POST /prototypes/render`（spec 5.1 的 AI 迭代用端點）—— 尚未需要，agent 目前直接改檔＋看頁面

### 3.2 Prototype 目錄與範例
- [x] `prototypes/project-a/{pages,fragments,fixtures,_hosts}/`
- [x] `pages/member-list.blade.php`（整頁）
- [x] `fragments/member-list.filters.blade.php`（帶 manifest）
- [x] `fixtures/{member-list,member-status}.php`
- [x] `_hosts/members-index.html`（宿主快照示範）
- [x] `.gitattributes` 加 `/prototypes` export-ignore（1.6 已加）

### 3.3 Fixture 載入（**spec 有缺陷，已修正**）
- [x] `@piFixture($x, 'name')` directive + `src/Prototype/FixtureLoader.php`
- [x] spec 5.2 的 `@php($x = include __DIR__.'/…')` 不可能運作：Blade 編譯成 `storage/framework/views/` 快取檔後，`__DIR__` 指向快取目錄
- [x] 路徑由 render 端設定，render 後清空（避免跨 request 讀到別的專案的 fixture）

### 3.4 Slot marker（**spec 有缺陷，已修正**）
- [x] 改用 HTML 註解 `<!-- @pi-slot: name -->`
- [x] spec 6.3 的 blade 註解 render 後會消失 → 宿主快照裡沒有錨點 → fragment 無處可插
- [x] 同一個 marker 同時服務 `apply.php`（改 blade 原始碼）與 fragment 注入（改 rendered HTML）

### 3.5 `scripts/fetch-host.php`
- [x] 抓 URL 存進 `_hosts/`
- [x] 站根相對路徑改寫為絕對 URL（**刻意不下載 CSS**，讓快照樣式跟著專案走）
- [x] 找不到 slot 錨點時警告並 exit 1
- [x] 需要登入的頁面給明確指引（改用瀏覽器另存）
- [x] 快照不寫時間戳（避免每次重抓都產生 diff）

### 3.6 `scripts/apply.php`
- [x] 讀 manifest 取 target / slot
- [x] 移除 `@piFragment` 與 `@piFixture`，並驗證無殘留
- [x] `--output=blade`（可貼上的片段）
- [x] `--output=patch --target=…`（unified diff，依 marker 縮排對齊）
- [x] **不直接寫入專案檔** —— 跨 repo 自動改檔風險太高，交 patch 讓前端 review
- [x] patch 的 `a/` `b/` 標籤依 target 的 git root 推導，`git apply` 對得上
- [x] 實測 `git apply --check` 與實際 apply 都通過

### 3.7 Phase 3 驗收
- [x] 完整跑過一次 fragment 流程（manifest → 宿主注入 → apply → git apply）
- [x] `CHANGELOG.md` 記錄
- [x] `git commit`
- [x] **`pm-to-preview` skill 改產 blade**（見 3.8）
- [x] page-scoped SCSS 門檻進 CI（見 3.12）

### 3.12 30 行門檻進 CI
- [x] `src/Prototype/StyleBudget.php` —— 門檻的**唯一計算來源**，preview 清單頁與 CI 共用
- [x] `src/Prototype/Manifest.php` —— manifest 解析同樣抽成共用（原本 preview 與 CI 各一份）
- [x] `scripts/check-prototypes.php` —— 樣式門檻 + fragment manifest，`--path` 可指向別的目錄
- [x] `.github/workflows/ci.yml` —— **repo 原本完全沒有 CI**，四個 gate：SCSS build / 元件清單同步 / prototype 檢查 / composer validate
- [x] `PrototypeCatalog` 移除自己的兩份實作，改用共用類別
- [x] 文件同步：CLAUDE.md 第 3 條、README 新增「CI 的四個 gate」、STRUCTURE 補 `src/` 樹狀圖與新 script、skill 三處

**三處刻意偏離請求範圍（review 點出，記錄在此而非默默夾帶）**

1. **門檻定義從「`<style>` 行數」擴大為「`<style>` 行數 + inline `style=""` 宣告數」。**
   使用者只要求「把門檻進 CI」，沒要求改定義。但不計 inline 的話，把樣式搬進
   attribute 就能繞過，閥門形同不存在（先前 review 實際抓到 `<div style="height: 24px">`
   完全不計入）。**實測無回歸**：現有 5 支最高 11 行，`project-a/member-list` 由 0 變 4。
   連帶：`CLAUDE.md` 第 3 條的文字從「page-scoped SCSS」改為「page-scoped 樣式」，
   但 `design-guideline-spec.md` §5.6 的規則原文仍是舊措辭 —— **兩份文件現在不一致**，
   要改 spec 需另行確認（那是 spec 原文，不是本 repo 的偏離記錄）。

2. **spec §8.6 原文是「超過 30 行就要 review」，不是 block。** 改成 CI 硬擋是立場翻轉。
   使用者說「進 CI」可支持硬擋，但這個轉變原本沒有記在任何地方（`PrototypeCatalog`
   裡「這是健康指標而不是硬性阻擋」的註解也被一併刪掉了）。現在記在這裡。

3. **CI 的另外三個 gate（SCSS build / 元件清單 / composer validate）與 30 行門檻無關。**
   最小可行只需一個 job。理由是 repo 原本沒有任何 CI，只放一個 job 等於白建一次
   workflow；四個 gate 本機都實跑 exit 0。**副作用**：從此 PR 會因與門檻無關的
   SCSS smoke 失敗而被擋。

**未完成（spec §5.6 只做到一半）**：spec 寫的是「`sync-component-list.php` 掛進 CI，
**PR 時自動更新** COMPONENTS 區段」。目前只做 `--check`（不一致就擋），不是自動 commit
回 PR —— 那需要 CI 有寫入權限，屬另一個決定。

**Code review 抓到並修掉的 5 個 bug**（都是實測驗證，不是猜測）：
- `style='…'`（單引號）完全不計 → 門檻可繞。實測 fixture 修正前計 0、修正後計 3
- `data-style="…"` 與 `:style="…"`（blade 綁定）被誤計 → 加 `(?<![-:\w])` 排除
- `<style>` 區塊內出現 `style="` 字串會被重複計 → 計 inline 前先剝掉 `<style>` 區塊
- 未閉合的 `<style>` 整段不計入 → 計到檔尾（實測 40 行修正前計 0、修正後計 40）
- manifest 檢查跑在整份原始碼而非 manifest 區塊內 → blade 註解寫 `'slot' => 'x'` 就誤判通過
- 邊界：`file_get_contents` 失敗回 `?: ''` 會讓所有檢查「通過」（CI 綠燈但什麼都沒檢查）
  → 改為中止；`preg_match_all` 回 `false` 與 `0` 原本走同一條路 → 分開處理
- `sort($prototypes)` 是拿整個 assoc array 比大小 → 改 `usort` 明確按 label

### 3.11 清掉無用與過時的檔案（共 39 檔）
- [x] `assets/logo.svg`（34 KB）—— 唯一引用者是已刪的 `preview-static/index.html`。**`assets/` 會出貨**，等於每個專案都拿到一支沒人用的 34 KB
- [x] `assets/icons-preview.html` —— 手維護的 glyph 索引，已由 `/foundation/icons` 取代（掃 JSON 生成）。留著就是第二個要記得同步的地方
- [x] `assets/noise.svg` —— 查全 git 歷史，從未被任何檔案引用
- [x] `resources/views/components/.gitkeep` —— 該目錄已有 26 檔
- [x] `.scratch/pm-to-blade-skill/`（7 檔）—— `pm-to-blade` 舊 skill 草稿，description 開頭就寫「用 **vendored** 的 Pi DS」，含 `vendor-copy.sh`（D1 排除的做法）
- [x] `.scratch/pm-to-preview-test/`（4 檔）、`.scratch/prototype-handoff/`（8 檔）—— 舊路線的測試輸出與 issue 記錄
- [x] `docs/agents/domain.md` 與 `triage-labels.md` —— 標準是「本專案實際有在用嗎」。domain docs 的 `CONTEXT.md` / `docs/adr/` 從未存在（架構決策記在 `design-guideline-spec.md` 的 D1–D6）；triage-labels 只為了 5 個字串獨立一支檔、且左右欄完全相同，已併進 `issue-tracker.md`。`issue-tracker.md` 保留 —— `.scratch/<feature>/` 就是那個慣例，實際有在用
- [x] `preview/` 的 Laravel skeleton 殘留（16 檔）：`app/Models/User.php`、`config/{auth,mail,filesystems,database}.php`、`database/{factories,migrations,seeders}`、`tests/`、`phpunit.xml`

**刪 DB 相關檔案前必須先切 driver。** 原本 session / cache / queue 全部預設 `database`（sqlite），直接刪 migration 會讓 preview 500。

處理方式是**把 driver 寫進 committed 的 config（`config/{session,cache,queue}.php`），且刻意不留 `env()` 逃生口**：
- `.env` 是 gitignored，改它對別人 clone 下來的環境無效
- `.env.example` 仍寫著 `SESSION_DRIVER=database`，而該檔受 hook 保護無法修改
- 寫死在 config 是唯一能讓「preview 不需要 DB」跟著 repo 走的方式

- [x] `package-lock.json` prune 掉已移除的 `vite` / `gsap`
- [x] `npm audit fix` —— `sass` 的傳遞依賴 `immutable` 有 1 個 high severity 漏洞，sass 1.77 → 1.100.0，修完 0 漏洞
- [x] 文件同步：README / STRUCTURE / CLAUDE / handoff-templates

**`.env.example` 仍過時**（寫著 `SESSION_DRIVER=database` 等），但受 hook 保護。因為 config 已寫死 driver，該檔的值不會生效，僅剩文件性的不一致 —— 要修需使用者手動處理。

### 3.10 README 過時資訊清理
- [x] **架構描述**：「不發布 npm；下游一律採 vendored」→ Composer 版本化依賴（vendored 是 D1 明確排除的方案，README 寫的跟現況正好相反）
- [x] 新增三節：Composer 安裝（含導入驗證與升級時機）、Blade 元件（含 tone 清單不一致與兩個已知缺口）、prototype 討論流程
- [x] **刪 `docs/prototype-README.template.md` 與 `prototype-CLAUDE.template.md`** —— 整份建立在已排除的架構上（vendored 進獨立原型 repo、產 blade+scss 手動複製進 production），且 `cp -R "$GUIDE/src"` 已是錯路徑。硬規則已由 `docs/ai-guide.md` 與 `CLAUDE.md` 覆蓋。**留著比刪掉危險** —— agent 讀到會照著重建 drift
- [x] 事實錯誤 7 處：symicon glyph 172 → **250**（兩處）、`$font-path` 範例 `"../../fonts"` → `"/fonts"`（與同節下方自相矛盾）、圓角漏 `--corner-shape`、「不用改 vite.config」（該檔已刪）、`type` 對照頁 → `/foundation/typography`、icons 對照頁 → `/foundation/icons`
- [x] icon 維護「情境 2」從 vendored 改寫為「字型與 class 表隨套件出貨，專案端不複製檔案」
- [x] 開頭加「兩種身份看不同小節」導引表
- [x] 同步 `STRUCTURE.md` 與 `SKILL.md` 的定位段（同樣寫著 vendored）
- [x] 驗證：9 個檔案連結全部存在、15 個錨點全部對得上標題

**順手修掉 `docs/ai-guide.md` 一個會害人的規則**：硬規則表教 `@extend .fz-body-sm;` —— `@extend` 會讓樣式逃出 `@layer pi`（Phase 1.1 已實測），照做會破壞隔離。已改為 class 或 `$fz-*` 變數，並註明不要用 `@extend`。同表的「自己寫 button / modal」也改成指向 `<x-pi::*>` 元件。

### 3.9 Foundation 頁搬進 blade preview，廢除 `preview-static/`
- [x] `TokenCatalog`：從 `resources/scss/tokens/*.scss` 解析 **170 個 token 名稱**（與 `dist/pi-ds-tokens.css` 的集合完全一致，已比對）
- [x] **值由瀏覽器 `getComputedStyle` 讀**，不由後端算 —— SCSS 裡的宣告是 `--cl-basic-900: #{$_cl-basic-900-raw};`，靜態解析拿到的是插值字串；解析 `dist/` 又要求先跑 build（那是 gitignore 的產物）。名稱來自原始碼、值來自瀏覽器，表格與色塊必然同源
- [x] `IconCatalog`：`assets/icon-names.json`（250）+ `icon-cp-map.json`。缺 codepoint 會在頁面上標紅（目前 0 個）
- [x] `FoundationController` + 6 支 view（index / tokens / group / icons / _sidebar / _token-table / _scripts）
- [x] 路由：`/foundation`、`/foundation/tokens`、`/foundation/icons`、`/foundation/{group}`（7 個群組）。**tokens 與 icons 必須宣告在 `{group}` 之前**，否則會被當成群組名吃掉
- [x] `/` 改成三區入口（原本 redirect 到 `/prototypes`）
- [x] `_chrome.scss` 補色塊 / 搜尋框 / icon grid（全部 `pv-` 前綴，不進 `@layer pi`）
- [x] **刪 `preview-static/`（20 支 HTML + preview.scss + 7 張截圖）**，含 3 支 `example-*.html`（舊路線 prototype，使用者確認可刪）
- [x] 刪根目錄 `vite.config.js`；`package.json` 移除 `dev` / `preview:build` / `preview:serve` 與 `vite` / `gsap`（`gsap` 只有 `preview-static/modal.html` 在用）
- [x] `.gitignore` 移除 `dist-preview/` 與 `preview-static/_shot_*.png`；刪 `dist-preview/`
- [x] 同步 19 處引用：README / STRUCTURE / SKILL / docs/ai-guide / docs/prototype-flow / preview/README / 兩支 skill / 11 支元件的 markup 出處註解
- [x] **結果：只剩 8100 + 5178 兩個服務**，repo 根目錄的 `npm` 只剩 SCSS build / lint

**實作中修的一個自找的坑**：icons 頁的用法範例寫成 `<code>&lt;i class="icon icon-<em>name</em>"&gt;</code>` —— raw HTML 裡出現 `class="` 字面，讓 class 比對工具誤判成真的 attribute。引號也要 `&quot;` 轉義。

### 3.8 `pm-to-preview` skill 改走 blade 路線
- [x] `SKILL.md`：第 3 步改產 `prototypes/<project>/`；刪掉「兩處註冊」（清單頁掃檔生成，不需註冊）
- [x] 第 1 步新增「判定 page 還是 fragment」與「確認專案名」
- [x] 第 2 步要求先讀 `.meta.php` 確認 props 值域（各元件 tone/size 清單不一致）
- [x] `references/preview-setup.md` → `prototype-setup.md`（整份重寫）
- [x] `references/handoff-templates.md`：元件清單欄位由 `Pi DS class` → `Pi DS 元件 + props`；後端「資料欄位清單」改為指向 fixture 檔；新增「套用方式」段（`apply.php` 指令）與「Page-scoped SCSS」段
- [x] `references/missing-component.md`：placeholder 改 blade 寫法；新增「先查 CLAUDE.md 已知缺口」
- [x] `.scratch/` 六份舊路線 handoff 加「舊路線產物，不要照抄」標記
- [x] **端到端實跑**：`prototypes/project-a/pages/salary-report.blade.php` + 2 支 fixture + `.scratch/salary-report/` 兩份 handoff（新的回歸基準）
- [x] **實跑抓到的 bug**：`apply.php` 只支援 fragment（只找 `fragments/`、且強制要 slot），但 skill 文件寫了 page 也能用 → 已加 page 支援
- [x] **實跑抓到的 bug**：殘留檢查誤判 —— prototype 的 blade 註解提到 `@piFixture` 就被當成殘留 → 檢查前剔除 blade 註解，且只認帶括號的呼叫

---

## Review

（實作完成後填寫：變更摘要、遇到的問題與解法、Phase 3 待辦）
