# CLAUDE.md — 專案規則（每次對話自動載入）

## 語言
- 回覆**一律使用繁體中文**，禁止簡體字。

## 變數 / Token 規則（最高優先）
- **嚴格禁止自創任何變數、token、class 內容。** 只能使用設計系統中**實際存在**的 token / class。
- 不確定某個 token / class 是否存在時，先 `grep` 或讀 `resources/scss/tokens/` 、`resources/scss/components/` 確認，**絕不憑記憶或推測發明**。
- 需要新的值時，先問使用者、或請使用者提供來源（Figma、原始 SCSS），不要自己編一個。
- 反例（過去發生過的錯誤）：自創 `$shadow-ring` —— 設計系統並沒有這個變數。「Ring」是 `.gl_shadow-*` 疊加 `.gl_border-outer` / `.gl_border-inner` 的組合。

## 命名空間規則（最高優先，與 Token 規則同級）

| 前綴 | 誰的 | 套件能收嗎 |
|---|---|---|
| `gl_` `fz-` `text-` `icon-` | **公共樣式**（各專案共用）—— 設計系統是它的單一來源 | ✅ 這就是套件的內容 |
| `iw_` `sa_` `ta_` … | **各專案私有**（interview / salary / task 各自的前綴） | ❌ **絕對不行** |
| `pv-` | preview app 的頁面殼 | ❌ 不出貨 |
| `pt-` | prototype 的 page-scoped 排版（**不分專案，一律 `pt-`**） | ❌ 不出貨 |

**第二列是硬規則。** 專案私有前綴不得出現在套件的任何地方 —— 不能定義它、也不能用
屬性選擇器（`[class*="iw_"]`）匹配它。

違反的後果是雙向的：其他專案裝了套件會拿到對它們毫無意義的 class；而該專案的私有
命名空間被套件佔用，之後自己要改就會撞。

實際發生過（0.0.2 已修）：`.iw_pagination-outer-v3` 被收進套件，理由是「對齊生產
環境既有命名」，連帶 `_component-base.scss` 用 `[class*="iw_"]` 匹配它 —— 結果套件
對 interview **全站**的 `iw_*` 元素套了 `corner-shape` 與 `font-weight: 500`。
「對齊專案既有命名」聽起來合理，但它讓套件變成某個專案的延伸，不是公共資產。

### `gl_` 撞名不是巧合，是預期狀態

各專案本來就有自己那份公共樣式拷貝（`gl_` = global）。Pi DS 的目的是**取代**它，
不是與它並存。`_layers.scss` 讓專案未分層的那份永遠勝出，所以：

- 裝了套件**不會動到既有畫面** —— 這是刻意的零風險導入
- 但同名 class 的新樣式也**吃不到**，包括新頁面（攔截在 class 層級，不是頁面層級）

專案要真正吃到 Pi DS 的元件樣式，只有兩條路（見 `index.scss` 檔頭的三個方案）：

1. **移除專案自己那份 `gl_`** → 完整導入
2. **只吃 tokens**（`@use ".../tokens"` 而非 `index`）→ 統一 token、元件維持專案自己的

**不要建議「改前綴避開撞名」** —— 那會讓套件變成第三套公共樣式，撞名沒解決只是繞開。

## 修改範圍
- 使用者要求小修改時，只動該處，不要順手「改善」其他部分。
- 改 token / class 命名屬於 breaking change，需同步更新所有引用處與文件（README、SKILL.md、preview）。

## Prototype 硬性規則（手寫，勿刪）

產 prototype（`prototypes/` 底下的 blade）時，以下七條沒有例外：

1. **只能用 `<x-pi::*>` 元件**，不准手刻 HTML 或裸 `gl_` class。
2. **資料一律放 `fixtures/`**，blade 內不得寫死。fixture 的 key 結構就是給後端的資料契約。
3. **page-scoped 樣式上限 30 行。超過即視為元件缺口 —— 停下來提報，不要自己刻。**
   計算方式：`<style>` 區塊的非空白行數 **＋ inline `style="…"` 的宣告數**（以 `;` 拆）。
   inline 也計入，否則把樣式搬進 attribute 就能繞過門檻，閥門直接失效。
   **由 CI 強制**（`scripts/check-prototypes.php`）—— 超標時要提報缺口，不是把門檻調高。
4. **page-scoped 的 class 一律 `pt-` 開頭，不分專案。** 其餘只准設計系統的
   `gl_` / `fz-` / `text-` / `icon-`。**由 CI 強制**（同一支腳本）。
   不要用專案私有前綴（`iw_` / `sa_` …），也絕不用 `pv-`（那只存在於 preview app，
   貼進專案會完全沒樣式）。
5. 不得輸出裸元素選擇器（`h1` / `a` / `button` / `*`）。
6. Fragment 必須宣告 `@piFragment` manifest（target / slot / host）。
7. 元件樣式一律包在 `@layer pi` 內，class 前綴 `gl_`。

第 3 條是整套流程的品質閥門。PM 不會知道什麼做得到什麼做不到；如果 agent 遇到缺口就自己刻一個出來，元件庫的缺口就被藏起來了，drift 從這裡開始。

## 可用元件清單

<!-- COMPONENTS:START -->
> 本區段由 `scripts/sync-component-list.php` 自動生成，請勿手動修改。
> 資料來源：`resources/views/components/*.meta.php`

共 13 支元件。各元件可用的 prop 值請看 preview 的元件頁（`/components/<name>`）或對應的 `.meta.php`。

| Tag | 名稱 | 說明 |
|---|---|---|
| `x-pi::alert` | Alert | 浮出式提示條。深色底、圓形 icon 底色隨 tone 變化，最小寬 352px。 |
| `x-pi::button` | Button | 操作按鈕。5 種 variant × 8 種 tone × 5 種尺寸，另有純 icon 型。 |
| `x-pi::callout` | Callout | 頁面內的提示區塊。左側圓形 icon 底色隨 tone 變化。 |
| `x-pi::checkbox` | Checkbox | 核取方塊。勾選色隨 tone 變化，勾勾是內嵌 SVG（不吃 icon 字型）。 |
| `x-pi::content-switcher` | Content switcher | 分頁式切換導覽。作用中的項目下方有短底線指示器。 |
| `x-pi::dropdown-item` | Dropdown item | 浮出選單內的單一項目。兩種尺寸，可帶前置 icon。 |
| `x-pi::form-control` | Form control | 表單欄位。含 input / select / textarea、前置 icon、說明文字與驗證回饋。 |
| `x-pi::loading` | Loading | 載入中轉圈。SCSS 只負責旋轉動畫，尺寸與顏色由使用端指定。 |
| `x-pi::modal` | Modal | 對話框面板。352px 寬、24px 圓角、白底加 xl 陰影。 |
| `x-pi::notification` | Notification item | 通知列表的一列。底部有分隔線，多列直接疊放即成列表。 |
| `x-pi::pagination` | Pagination | 頁碼列。當前頁深色實心，首/末頁時箭頭停用。≤768px 自動縮為 32px。 |
| `x-pi::radio` | Radio | 單選鈕。選中色隨 tone 變化，圓點是 ::after 疊在 ::before 上。 |
| `x-pi::toggle` | Toggle | 開關。純 CSS 實作（input + knobs + layer 三層），無需 JS。 |

### 已知元件缺口（不要自己刻，走缺口流程提報）

- `x-pi::dropdown-item` — 設計系統只有「選單項目」的樣式，沒有浮出面板本身（定位 / 陰影 / 開關行為）—— 這是已知元件缺口。需要完整 dropdown 請走缺口流程提報，不要自行新增 .gl_dropdown。
- `x-pi::modal` — 這支只有「面板外觀」。遮罩、置中、開關、focus trap 都不在設計系統內 —— 需要時用原生 <dialog> 包住，或走元件缺口流程提報。
<!-- COMPONENTS:END -->

## Agent skills

### Issue tracker

Local markdown — issue 存為 `.scratch/<feature>/` 下的 markdown 檔，triage 狀態寫成
`Status:` 行。慣例與五個 canonical 狀態字串見 `docs/agents/issue-tracker.md`。

### 本專案不採的兩個慣例

`docs/agents/` 原本還有 `domain.md` 與 `triage-labels.md`（同一套 agent skills 帶進來的），
已移除 —— 標準是「本專案實際有在用嗎」：

- **domain docs**（`CONTEXT.md` + `docs/adr/`）：架構決策記在
  `design-guideline-spec.md` 的 D1–D6、偏離與理由記在 `TODO.md`，術語不需要獨立 glossary。
  那兩個檔案從未存在過。
- **triage labels**：獨立一支檔只為了記 5 個字串，而且左右欄完全相同（沒有做任何映射）。
  字串已直接併進 `issue-tracker.md`，少一層跳轉。

若日後要用 `/domain-modeling` 這類 skill，再按它的需求建立對應文件即可。
