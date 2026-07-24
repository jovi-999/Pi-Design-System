---
name: pm-to-blade
description: 從 PM 的自然語言／口述需求，用 vendored 的 Pi Design System 既有元件產出一份可跑的 Blade prototype（Blade view + prototype SCSS），並同步輸出前端／後端兩份交棒規格（handoff），供前後端接力做實際版面與功能。觸發：PM 用文字描述一個頁面／畫面想先看 prototype、要把需求交棒給前後端、或說「做個 prototype／原型」「幫我把這段需求變成畫面 + 交接文件」時。輸入是 Figma 連結時不走本 skill。
---

# PM 需求 → Blade Prototype + 交棒規格（Laravel + Vite）

把 PM 的一段文字需求，變成「一份可跑的 Blade prototype（`.blade.php` + prototype `.scss`）」＋「前後端各一份交棒規格」。前後端接手後，直接拿這份 Blade + SCSS 去套實際流程版面與功能。

**最高原則：只用 vendored Pi DS 實際存在的 token / class，嚴禁自創 —— 完整規則見 [references/token-rules.md](references/token-rules.md)，開工前先讀。** 不確定就 `grep` 或讀 vendored `pi-ds/` 確認；需要的東西不存在時走缺件流程（第 2 步），不要自己編一個。

## 前置假設（新專案需先備妥；路徑可依專案調整，調了要同步改本 skill）

- **Vendored Pi DS**：Pi DS 的 `src/` 已複製進 `resources/sass/pi-ds/`（tokens + base + components + index.scss）。
- **編譯入口**：`resources/sass/app.scss` 內 `@use 'pi-ds/index' as *;`（或 `@forward`）；`vite.config.js` 的 `input` 已含 `resources/sass/app.scss`。icon 字型 `symicon.css` 已引入。
- **跑法**：`npm run dev`（Laravel Vite HMR）+ `php artisan serve`。
- **prototype 落點**：Blade view → `resources/views/prototypes/<name>.blade.php`；組合件樣式 → `resources/sass/prototypes/_<name>.scss`（由 app.scss `@use`）。細節見 [references/blade-setup.md](references/blade-setup.md)。

## 何時用、何時不用

- **用**：輸入是 PM 自由文字／口述需求，要先產可跑 Blade prototype + 交棒給前後端。
- **不用**：輸入是 Figma 連結（含 `node-id`）→ 走 Figma 落地流程；只是丟棄式試驗、不需交棒 → 用一般 prototype 流程。

## 流程（五步，做完一步再下一步）

### 1. 收描述 → 回述確認（不讓 PM 填表）

PM 自由文字／口述描述，**不給模板、不要 PM 填欄位**。AI 解析後回述一份**結構化摘要**給 PM 確認／更正，摘要**只含 PM 判斷得出的兩類**：

1. **解讀到的元件** — 對到 vendored Pi DS 實際 class（例：「選單」→ 確認是 `gl_dropdown` 面板還是原生 `select.gl_form-control`）。
2. **版面／排列關係** — 誰在誰上下、幾欄。

**PM 責任邊界**：只確認元件 + 版面。**狀態（error / disabled / 送出後）與資料行為（選項來源、送去哪）PM 不懂，不要問 PM**，直接在產物標「待前後端補」（標記見第 4 步）。可多輪回述至 PM 點頭；元件／版面歧義在此收斂。

> 這份回述摘要的「元件 + 版面」結構 = 第 4 步前端 handoff 的「版面結構 + 元件清單」兩段，別重做。

### 2. 元件對應 + 缺件判斷（三層門檻）

逐個把需求元件過**三層門檻**：命中現有件直接用／現有 token 疊得出＝組合件（標 `⚠️`，不提議）／皆不能＝真缺件走提議 loop。缺件提議 4 欄、PM 審批 3 種、placeholder 畫法**全部照 [references/missing-component.md](references/missing-component.md)**。無回應時停等 PM。

### 3. 產出可跑 Blade prototype

- **Blade view** `resources/views/prototypes/<name>.blade.php`：extend prototype layout（含 `@vite`），只用 vendored `gl_*` class。**產物是乾淨、可被前端直接抬走的頁面標記**，不要包 demo 展示殼。
- **組合件 SCSS**（門檻第 2 層才需）：`resources/sass/prototypes/_<name>.scss`，class 名 prototype-scoped（如 `.proto-<name>-banner`），**值一律用現有 Pi DS token（`@use 'pi-ds/tokens' as *;`）** —— 這是組合，不是自創（見 token-rules）。避免大量 inline style，讓前端好接。
- **命中現有件**直接用 `gl_*` class，不需額外 scss。
- 落點、layout、route、`npm run dev` 驗證**全部照 [references/blade-setup.md](references/blade-setup.md)**。收尾：`npm run dev` + `php artisan serve`，開該 route 確認頁面 render、無 Vite/Sass error。

### 4. 產出兩份交棒規格（前端 + 後端各一）

兩份分開 markdown。標記約定（`⚠️ 待確認` / `🔧 待補` / `🆕 待確認`）與完整模板**全部照 [references/handoff-templates.md](references/handoff-templates.md)**。前端「版面結構 + 元件清單」沿用第 1 步回述摘要；「Preview」段填 Blade 路徑 + route。

### 5. Capture（落檔）

- Blade view + prototype scss 已在第 3 步落 `resources/`。
- 兩份 handoff 落該需求的專案 issue 目錄（或使用者指定處）。
- 全程零自創：交付前再掃一遍，確認無新 token 值 / 假 `gl_` class（見 token-rules）。

## 自檢

- [ ] 回述摘要只含元件 + 版面，狀態/資料未問 PM
- [ ] 每個元件過了三層門檻；缺件有走提議 loop 或標組合件
- [ ] Blade prototype 能 render、無 Vite/Sass error、route 可開
- [ ] 組合件樣式用 prototype-scoped scss + 現有 token（非一堆 inline）
- [ ] 前後端兩份 handoff 齊、標記正確
- [ ] 零自創（無新 token 值 / 假 gl_ class）—— 對照 token-rules.md
