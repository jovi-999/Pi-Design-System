---
name: pm-to-blade
description: 從 PM 的自然語言／口述需求，用 vendored 的 Pi Design System 既有元件產出一份可跑的 Blade prototype（Blade view + 一支獨立 preview SCSS），並同步輸出前端／後端兩份交棒規格（handoff），供前後端接力做實際版面與功能。觸發：PM 用文字描述一個頁面／畫面想先看 prototype、要把需求交棒給前後端、或說「做個 prototype／原型／preview」「幫我把這段需求變成畫面 + 交接文件」時。輸入是 Figma 連結時不走本 skill。
---

# PM 需求 → Blade Prototype + 交棒規格（Laravel + Vite）

把 PM 的一段文字需求，變成「一份可跑的 Blade prototype（`.blade.php` + 一支獨立 `preview-<name>.scss`）」＋「前後端各一份交棒規格」。前後端接手後，直接拿這份 Blade + SCSS 去套實際流程版面與功能。

**最高原則：只用 vendored Pi DS 實際存在的 token / class，嚴禁自創 —— 完整規則見 [references/token-rules.md](references/token-rules.md)，開工前先讀。** 不確定就 `grep` 或讀 vendored `pi-ds/` 確認；需要的東西不存在時走缺件流程（第 2 步），不要自己編一個。

## 前置假設（新專案需先備妥；路徑不同時改本段 + blade-setup.md）

- **Vendored Pi DS**：Pi DS 的 `src/` 已複製進 `resources/sass/pi-ds/`（tokens + base + components + index.scss）。
- **Sass 解析**：Vite 的 `css.preprocessorOptions` 有 `loadPaths: ['resources/sass']`，故各 scss 內 `@use 'pi-ds/index' as *;` 直接解得到（免 alias）。若無此 loadPath，改用相對路徑 `@use 'pi-ds/index'`。
- **建置慣例**：本專案是**一頁一支 scss**，各入口列在 `vite.config.*` 的 `input`。**不使用共用 app.scss** —— 每個 prototype 產自己的 `preview-<name>.scss`，避免與專案既有檔撞名。
- **跑法**：Laradock 內 `docker-compose exec workspace` → `npm run watch`（= `vite`）。
- 落點與註冊細節見 [references/blade-setup.md](references/blade-setup.md)。

## 何時用、何時不用

- **用**：輸入是 PM 自由文字／口述需求，要先產可跑 Blade prototype + 交棒給前後端。
- **不用**：輸入是 Figma 連結（含 `node-id`）→ 走 Figma 落地流程；只是丟棄式試驗、不需交棒 → 用一般 prototype 流程。

## 流程（五步，做完一步再下一步）

### 1. 收描述 → 回述確認（不讓 PM 填表）

PM 自由文字／口述描述，**不給模板、不要 PM 填欄位**。AI 解析後回述一份**結構化摘要**給 PM 確認／更正，摘要**只含 PM 判斷得出的兩類**：

1. **解讀到的元件** — 對到 vendored Pi DS 實際 class（例：「選單」→ 確認是 `gl_dropdown` 面板還是原生 `select.gl_form-control`）。
2. **版面／排列關係** — 誰在誰上下、幾欄。

**同時請 PM 給這份 prototype 的名字**（用於檔名，前綴固定 `preview-`，如 `preview-signup`）。

**PM 責任邊界**：只確認元件 + 版面（+ 命名）。**狀態（error / disabled / 送出後）與資料行為（選項來源、送去哪）PM 不懂，不要問 PM**，直接在產物標「待前後端補」（標記見第 4 步）。可多輪回述至 PM 點頭。

> 回述摘要的「元件 + 版面」結構 = 第 4 步前端 handoff 的「版面結構 + 元件清單」兩段，別重做。

### 2. 元件對應 + 缺件判斷（三層門檻）

逐個把需求元件過**三層門檻**：命中現有件直接用／現有 token 疊得出＝組合件（標 `⚠️`，不提議）／皆不能＝真缺件走提議 loop。缺件提議 4 欄、PM 審批 3 種、placeholder 畫法**全部照 [references/missing-component.md](references/missing-component.md)**。無回應時停等 PM。

### 3. 產出可跑 Blade prototype（檔名用 PM 指定的 `preview-<name>`）

三件事，全部用 `preview-<name>` 這個名字：

1. **獨立 SCSS 入口** `resources/sass/preview-<name>.scss`：
   ```scss
   @use 'pi-ds/index' as *;   // tokens + base + components（gl_* class 可用）
   // 組合件（門檻第 2 層才需）：class prototype-scoped，值全用現有 token
   // .preview-<name>-banner { background: var(--cl-basic-900); border-radius: var(--radius-md); }
   ```
   **絕不寫進共用 app.scss** —— 一支獨立檔，用完可刪，零污染。
2. **Blade view** `resources/views/prototypes/preview-<name>.blade.php`：載自己那支 scss，內容是**乾淨、可被前端直接抬走的頁面標記**，只用 vendored `gl_*` class，不包 demo 展示殼。
   ```blade
   <!doctype html><html lang="zh-Hant"><head>
     <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
     @vite(['resources/sass/preview-<name>.scss'])
   </head><body>
     {{-- 頁面標記；只用 gl_* class --}}
   </body></html>
   ```
3. **註冊 + 路由**：把 `resources/sass/preview-<name>.scss` 加進 `vite.config.*` 的 `input`；加一條 route（見 blade-setup.md）。

命中現有件直接用 `gl_*` class；組合件樣式進該支 preview scss（非一堆 inline）。細節、route 寫法、跑法**全部照 [references/blade-setup.md](references/blade-setup.md)**。收尾：Laradock 內 `npm run watch`，開該 route 確認頁面 render、無 Vite/Sass error。

### 4. 產出兩份交棒規格（前端 + 後端各一）

兩份分開 markdown。標記約定（`⚠️ 待確認` / `🔧 待補` / `🆕 待確認`）與完整模板**全部照 [references/handoff-templates.md](references/handoff-templates.md)**。前端「版面結構 + 元件清單」沿用第 1 步回述摘要；「產物」段填 `preview-<name>` 的 blade / scss 路徑 + route。

### 5. Capture（落檔）

- Blade view + preview scss 已在第 3 步落 `resources/`。
- 兩份 handoff 落該需求的專案 issue 目錄（或使用者指定處）。
- 全程零自創：交付前再掃一遍，確認無新 token 值 / 假 `gl_` class（見 token-rules）。

## 自檢

- [ ] 回述摘要只含元件 + 版面 + PM 指定的 `preview-<name>` 命名
- [ ] 每個元件過了三層門檻；缺件有走提議 loop 或標組合件
- [ ] 產物用獨立 `preview-<name>.scss`（**沒碰 app.scss 或既有頁面 scss**）
- [ ] scss 已加進 vite input、route 可開、`npm run watch` 無 Vite/Sass error
- [ ] 組合件樣式用 prototype-scoped class + 現有 token（非一堆 inline）
- [ ] 前後端兩份 handoff 齊、標記正確
- [ ] 零自創（無新 token 值 / 假 gl_ class）—— 對照 token-rules.md
