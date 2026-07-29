---
name: pm-to-preview
description: 從 PM 的自然語言／口述需求，用 Pi Design System 既有的 <x-pi::*> Blade 元件產出一份可跑的 prototype，並同步輸出前端／後端兩份交棒規格（handoff）。觸發：PM 用文字描述一個頁面／畫面想先看 prototype、要把需求交棒給前後端、或說「做個 preview／原型」「幫我把這段需求變成畫面 + 交接文件」時。輸入是 Figma 連結時改用 figma-to-pi-ds，不走本 skill。
---

# PM 需求 → Pi DS Prototype + 交棒規格

把 PM 的一段文字需求，變成「一份可跑的 blade prototype」＋「前後端各一份交棒規格」。

**最高原則（來自專案 `CLAUDE.md`）：只用實際存在的元件與 token / class，嚴禁自創。** 不確定就先讀 `resources/views/components/<name>.meta.php`（元件的 props 與注意事項）或 `resources/scss/`，需要的東西不存在時走缺件流程（第 2 步），不要自己編一個。

## 為什麼產物是 blade 而不是 HTML

裸 HTML 回專案時要反向對應回元件，AI 會猜錯；而且元件的內部 DOM 會被寫死進專案 —— 日後 guideline 改元件結構，用 `<x-pi::*>` 的專案 `composer update` 就跟上了，轉出去的裸 HTML 永遠停在舊版。理由詳見 `design-guideline-spec.md` 的 D3 與附錄 B。

## 何時用這條、何時不用

- **用**：輸入是 PM 的自由文字／口述需求，要先產可跑 prototype + 交棒給前後端。
- **不用**：輸入是 Figma 連結（含 `node-id`）→ 走 `figma-to-pi-ds`；只是要一個丟棄式的 UI／邏輯試驗、不需交棒 → 走 `/prototype`。

## 流程（五步，做完一步再下一步）

### 1. 收描述 → 回述確認（不讓 PM 填表）

PM 用自由文字／口述隨意描述，**不給模板、不要 PM 填欄位**。AI 解析後回述一份**結構化摘要**給 PM 確認／更正，摘要**只含 PM 判斷得出的三類**：

1. **解讀到的元件** — 對到實際存在的 `<x-pi::*>`（例：「選單」→ 確認是 `<x-pi::dropdown-item>` 浮出選單，還是 `<x-pi::form-control type="select">`）。
2. **版面／排列關係** — 誰在誰上下、幾欄。
3. **這是整頁還是頁面中的一塊** — 決定產出 page 還是 fragment（見下方）。

**PM 責任邊界**：只確認元件、版面、page/fragment。**狀態（error / disabled / 送出後）與資料行為（選項來源、送去哪）PM 不懂，不要問 PM**，直接在產物標「待前後端補」（標記見第 4 步）。可多輪回述直到 PM 點頭；元件／版面歧義在此收斂。

> 這份回述摘要的「元件 + 版面」結構，就是第 4 步前端 handoff 的「版面結構 + 元件清單」兩段 —— 同一結構，別重做。

**同時要問清楚兩件 PM 一定知道的事**：

- **專案名**（決定落檔目錄 `prototypes/<project>/`）。不知道就問，不要自己編一個。
- **是 fragment 的話**：要插進專案的哪個頁面、該位置有沒有埋 slot marker。
  沒有 marker 就先跟 PM／前端要，或請他們埋一行 `<!-- @pi-slot: <slot-name> -->`。

Fragment 需要專案脈絡時，做法是**把專案頁面的 rendered HTML 抓回來當背景快照**（`fetch-host.php`，見 [prototype-setup.md](references/prototype-setup.md)），而不是把討論推回專案。

### 2. 元件對應 + 缺件判斷（三層門檻）

逐個把需求元件過**三層門檻**：命中現有件直接用／現有 token 疊得出＝組合件（標 `⚠️`，不提議）／皆不能＝真缺件走提議 loop。缺件提議 4 欄、PM 審批 3 種、placeholder 畫法**全部照 [missing-component.md](references/missing-component.md)**。無回應時停等 PM，不自作主張。

**先做兩件事再判斷**：

- 讀 `CLAUDE.md` 的可用元件清單（自動生成，永遠與實際程式碼一致）。
- 讀該元件的 `resources/views/components/<name>.meta.php` 確認 props 值域 ——
  **各元件的 tone / size 清單不一致**（例：button 有 8 個 tone、checkbox 只有 4 個、toggle 沒有 `xl`），不要靠記憶推。給錯值元件會直接丟例外。

`CLAUDE.md` 底部的「已知元件缺口」段落列的是**已經確認缺、不要自己刻**的東西，遇到直接走缺件流程。

### 3. 產出可跑 prototype

落在 `prototypes/<project>/`，四層結構。目錄、`@piFixture` / `@piFragment` 用法、slot marker 規則、preview 網址、`fetch-host.php` 何時跑 —— **全部照 [prototype-setup.md](references/prototype-setup.md)**。

要點：

- **只用 `<x-pi::*>`**，不手刻元件 markup、不寫裸 `gl_` class。
- **資料一律進 `fixtures/`**，blade 內不得寫死。fixture 的 key 結構就是給後端的資料契約。
- **page-scoped 樣式上限 30 行**（`CLAUDE.md` 第 3 條）= `<style>` 行數 + inline `style=""` 宣告數。超過即視為元件缺口 —— 停下來提報，不要自己刻。**CI 會擋**（`scripts/check-prototypes.php`）。行數會顯示在 `/prototypes` 清單頁。
- 不需要任何註冊 —— prototype 清單頁是掃檔生成的。

收尾：開 `http://localhost:8100/prototypes/<project>/<name>` 確認回 **200**、畫面正確。

### 4. 產出兩份交棒規格（前端 + 後端各一）

兩份分開的 markdown，前後端都有真人接棒。標記約定（`⚠️ 待確認` / `🔧 待補` / `🆕 待確認`）與兩份完整模板**全部照 [handoff-templates.md](references/handoff-templates.md)**。前端段的「版面結構 + 元件清單」直接沿用第 1 步回述摘要。

前端 handoff 必須含 `scripts/apply.php` 的指令 —— 那是把 fragment 套進專案的方式，不要讓前端自己複製貼上。

### 5. Capture（落檔）

- prototype blade 與 fixtures 落在 `prototypes/<project>/`（已在第 3 步）。
- 兩份 handoff 落在該需求的 `.scratch/<feature-slug>/`（或使用者指定處）。
- 全程零自創：交付前再掃一遍，確認無新 token / class / SCSS / 元件。

## 一次做完的自檢

- [ ] 回述摘要含元件 + 版面 + page/fragment 判定；狀態/資料未問 PM
- [ ] 專案名已確認（不是自己編的）
- [ ] 每個元件都過了三層門檻；缺件有走提議 loop 或標組合件
- [ ] 每個用到的元件都讀過 `.meta.php` 確認 props 值域
- [ ] blade 內零寫死資料，全部走 `@piFixture`
- [ ] `php scripts/check-prototypes.php` 通過（樣式 ≤ 30 行、fragment manifest 完整）
- [ ] `/prototypes/<project>/<name>` 回 200、畫面正確
- [ ] fragment：已注入宿主快照（不是顯示「未注入宿主快照」的裸片段）
- [ ] `php scripts/apply.php <project> <name>` 跑得起來
- [ ] 前後端兩份 handoff 齊、標記正確、前端含 apply 指令
- [ ] 零自創（無新 token / class / SCSS / 元件）
