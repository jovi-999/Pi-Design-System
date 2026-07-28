# Pi DS preview

Pi Design System 的 **blade preview 開發工具**。

不出貨、不部署。`.gitattributes` 已把 `/preview` 標為 `export-ignore`，
專案端 `composer require` 拿不到這個目錄。

---

## 這是什麼 / 不是什麼

| | |
|---|---|
| **是** | 一個本機 Laravel app，唯一工作是 render 套件裡的 `<x-pi::*>` blade 元件給人看 |
| **不是** | 公司其他專案那種 Laradock 服務。本 repo 是 Composer library，出貨的是 SCSS 原始碼 + blade 檔，沒有 build step、沒有資料庫、沒有 queue |

套件本體只依賴 `illuminate/support`；要 render blade 才需要完整 framework，
所以 framework 只裝在這個子目錄，不污染套件的依賴。

---

## 起動（兩步）

```bash
cd preview

docker compose up -d      # 1. PHP（container）→ http://localhost:8100
npm run dev               # 2. Vite（host）    → port 5178
```

第一次要先裝依賴：

```bash
docker compose run --rm app composer install
npm install
```

停止：

```bash
docker compose down
```

### Port

| Port | 服務 | 備註 |
|---|---|---|
| 8100 | PHP（`artisan serve`，container） | |
| 5178 | Vite（host），本目錄 | `strictPort` —— 撞號直接報錯，不靜默跳號 |
| 5177 | Vite（host），repo 根的 `preview-static/` | 兩者可同時開 |

公司其他專案的 Vite 都跑 5173，這裡刻意避開。

**為什麼 `strictPort`**：Vite 靜默跳號會把新 port 寫進 `public/hot`，
但容器內的 PHP 讀到的是那份檔，指向一個沒東西的 port —— 症狀是
「頁面完全沒有樣式」，而且不會有任何錯誤訊息，很難查。

---

## 架構：為什麼 PHP 在容器、Vite 在 host

```
Pi-Design-System/            ← 整個 repo 掛進容器的 /var/www
├── resources/scss/          ← 單一真相源（Vite 從 host 直接讀）
├── resources/views/         ← blade 元件（PHP 在容器內透過 symlink 讀）
└── preview/                 ← 本目錄，working_dir = /var/www/preview
    ├── public/hot           ← host 的 Vite 寫、容器內的 PHP 讀（bind mount 共享）
    ├── public/fonts   → ../../fonts
    ├── public/assets  → ../../assets
    └── vendor/company/pi-design-system → ../../..（composer path repository）
```

- **PHP 在容器**：統一 PHP 版本、附帶 composer（host 沒裝）
- **Vite 在 host**：`node_modules/` 已在 host，省一個 node container；
  macOS bind mount 的 inotify 不可靠，檔案監看留在 host 最穩
- **掛整個 repo 而不是只掛 `preview/`**：composer path repository 產生的
  symlink 指回 repo 根，只掛 `preview/` 會斷鏈

---

## 幾個刻意的設定

### 1. 移除了 Laravel 12 skeleton 的 Tailwind

Tailwind 的 preflight 會跟 Pi DS 的 `reset.scss` / `@layer pi` 打架。
Preview 必須只反映 Pi DS 本身的樣式，否則會引進整套流程最想避免的失敗：
**preview 看起來對、貼進專案走鐘**。

同時移除了 skeleton 的 `resources/js/bootstrap.js`（只做 axios 掛載，preview 用不到）。

### 2. SCSS 走實體相對路徑，不走 `vendor/` symlink

`resources/scss/app.scss`：

```scss
@use "../../../resources/scss/preview-all";
```

`vendor/company/pi-design-system` 是指回 repo 根的 symlink，而 repo 根底下
又有 `preview/` —— 讓 Vite 的 file watcher 走進去會無限遞迴。
所以 `vite.config.js` 的 `server.watch.ignored` 排除 `**/vendor/**`，
SCSS 這邊直接走實體路徑，HMR 才會正常觸發。

PHP 讀 blade 走 symlink 沒有這個問題（不涉及 file watcher）。

### 3. 套件版本約束是 `@dev` 而非 `*`

path repository 的版本來自分支名（`dev-main`），會撞 `minimum-stability: stable`。
這只是本機 path 安裝的特性 —— Phase 4 專案端走 VCS + tag，用的是 `^1.0`
這類穩定約束，不受影響。

### 4. 字型走 `public/` 的 symlink

`$font-path` 預設 `/fonts`（web root 起算的絕對路徑，因為 Sass 不會 rebase `url()`）。
所以 `public/fonts` 與 `public/assets` symlink 到 repo 根的同名目錄。

`npm run build` 會出現這行警告，是預期的：

```
/fonts/symicon-6.4s.woff referenced in ... didn't resolve at build time,
it will remain unchanged to be resolved at runtime
```

### 5. sqlite 檔

`composer create-project` 的 post-create script 會建 `database/database.sqlite`
並跑 migration。Preview 不需要 DB，該檔已 gitignore。

---

## 兩套 preview 的分工（過渡期）

| 目錄 | 內容 | 狀態 |
|---|---|---|
| `preview-static/` | 純 HTML + Vite 的視覺對照頁（20 支） | 現役 |
| `preview/` | 本目錄，blade 元件 preview | Phase 2.3 起接手 |

判定 `preview-static/` 可否廢除的條件見 `TODO.md` 的 Phase 2.7。
