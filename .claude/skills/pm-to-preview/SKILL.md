---
name: pm-to-preview
description: 從 PM 的自然語言／口述需求，用 Pi Design System 既有元件產出一份可跑的 HTML preview prototype，並同步輸出前端／後端兩份交棒規格（handoff）。觸發：PM 用文字描述一個頁面／畫面想先看 prototype、要把需求交棒給前後端、或說「做個 preview／原型」「幫我把這段需求變成畫面 + 交接文件」時。輸入是 Figma 連結時改用 figma-to-pi-ds，不走本 skill。
---

# PM 需求 → Pi DS Preview + 交棒規格

把 PM 的一段文字需求，變成「一個可跑的 HTML preview」＋「前後端各一份交棒規格」。

**最高原則（來自專案 `CLAUDE.md`）：只用 `src/tokens/`、`src/components/` 實際存在的 token / class，嚴禁自創。** 不確定就先 `grep` 或讀 `src/` 確認；需要的東西不存在時走缺件流程（第 2 步），不要自己編一個。

## 何時用這條、何時不用

- **用**：輸入是 PM 的自由文字／口述需求，要先產可跑 preview + 交棒給前後端。
- **不用**：輸入是 Figma 連結（含 `node-id`）→ 走 `figma-to-pi-ds`；只是要一個丟棄式的 UI/邏輯試驗、不需交棒 → 走 `/prototype`。

## 流程（五步，做完一步再下一步）

### 1. 收描述 → 回述確認（不讓 PM 填表）

PM 用自由文字／口述隨意描述，**不給模板、不要 PM 填欄位**。AI 解析後回述一份**結構化摘要**給 PM 確認／更正，摘要**只含 PM 判斷得出的兩類**：

1. **解讀到的元件** — 對到 Pi DS 實際 class（例：「選單」→ 確認是 `gl_dropdown` 面板還是原生 `select.gl_form-control`）。
2. **版面／排列關係** — 誰在誰上下、幾欄。

**PM 責任邊界**：只確認元件 + 版面。**狀態（error / disabled / 送出後）與資料行為（選項來源、送去哪）PM 不懂，不要問 PM**，直接在產物標「待前後端補」（標記見第 4 步）。可多輪回述直到 PM 點頭；元件／版面歧義在此收斂。

> 這份回述摘要的「元件 + 版面」結構，就是第 4 步前端 handoff 的「版面結構 + 元件清單」兩段 —— 同一結構，別重做。

### 2. 元件對應 + 缺件判斷（三層門檻）

逐個把需求元件過**三層門檻**：命中現有件直接用／現有 token 疊得出＝組合件（標 `⚠️`，不提議）／皆不能＝真缺件走提議 loop。缺件提議 4 欄、PM 審批 3 種、placeholder 畫法（含「無 dashed border token，禁自創」）**全部照 [references/missing-component.md](references/missing-component.md)**。無回應時停等 PM，不自作主張。

### 3. 產出可跑 preview

照 `preview/button.html` 範本建 `preview/<name>.html`，只引 `/src/index.scss`（設計系統唯一真相源），只用現有 `gl_*` class + 現有 CSS var。新頁需**兩處註冊**（`preview/index.html` 的 PAGES 陣列 + `src/components/index.scss`）。目錄、引用方式、class 慣例、`npm run dev` 跑法**全部照 [references/preview-setup.md](references/preview-setup.md)**。收尾用 `npm run dev` 開該頁確認回 **200**、`/src/index.scss` 編譯無 error。

### 4. 產出兩份交棒規格（前端 + 後端各一）

兩份分開的 markdown，前後端都有真人接棒。標記約定（`⚠️ 待確認` / `🔧 待補` / `🆕 待確認`）與兩份完整模板**全部照 [references/handoff-templates.md](references/handoff-templates.md)**。前端段的「版面結構 + 元件清單」直接沿用第 1 步回述摘要。

### 5. Capture（落檔）

- preview HTML 落在 `preview/`（已在第 3 步）。
- 兩份 handoff 落在該需求的 `.scratch/<feature-slug>/`（或使用者指定處）。
- 全程零自創：交付前再掃一遍，確認無新 token / class / SCSS。

## 一次做完的自檢

- [ ] 回述摘要只含元件 + 版面，狀態/資料未問 PM
- [ ] 每個元件都過了三層門檻；缺件有走提議 loop 或標組合件
- [ ] preview `npm run dev` 回 200、無編譯 error、preview/index.html 已註冊
- [ ] 前後端兩份 handoff 齊、標記正確
- [ ] 零自創（無新 token / class / SCSS）

> 已驗證範例（可當回歸基準）：輸入「navbar + 表單（姓名/Email input + 產業別 select + 送出鈕）」→ 產出 `preview/example-signup.html` + `.scratch/prototype-handoff/example/{frontend,backend}-handoff.md`。
