# 交棒規格模板（前端 + 後端）

**兩份分開的 markdown**（前端、後端各一），因為前後端都有真人接棒。

## 標記約定（兩份共用）

- `⚠️ 待確認` — AI 推測、需人確認的內容（狀態、互動、API 意圖）。
- `🔧 待補` — PM/AI 階段無法決定、由接棒者補（資料型別、選項來源、驗證規則）。
- `🆕 待確認` — AI 提議的新元件／新 token，尚未定案（觸發與審批見 [missing-component.md](missing-component.md)）。

## 前端 handoff 模板

必列：版面結構、元件清單、狀態+互動（頁面級+欄位級）、Prototype、套用方式、待確認新件。有才列：歧義收斂、組合件、page-scoped SCSS。

```markdown
# [頁面名] — 前端 Handoff

## 版面結構
<區塊樹 / 欄數 / 上下左右排列關係>

## 元件清單
| 實例 | Pi DS 元件 | props |
|------|-----------|-------|
| 主送出鈕 | `<x-pi::button>` | `tone="success" size="md"` |
| 關鍵字搜尋 | `<x-pi::form-control>` | `icon="icon-search" placeholder="姓名 · Email"` |

<!-- 交棒物是「可直接貼的 blade」而不是 class 清單 —— 元件內部 DOM 由套件負責，
     專案 composer update 就會跟上。不要列出 gl_ class。 -->

## 歧義收斂
<!-- 僅有元件歧義時列；記「PM 說法 → 收斂成哪個 Pi DS 元件 + 理由」。無歧義刪此段 -->
- 「選單」→ `<x-pi::form-control type="select">`（非 `<x-pi::dropdown-item>` 浮出選單）— 表單單選情境

## 組合件（⚠️ 待確認）
<!-- 缺件門檻第 2 層：無正式元件、用現有 token 疊出者。無則刪此段 -->
- <組合件名> — 用 <哪些 token> 疊出 — ⚠️ 待確認「組合而非正式元件」

## Page-scoped SCSS
<!-- 有寫 <style> 才列。上限 30 行（CLAUDE.md 第 3 條）。行數也會顯示在 /prototypes 清單頁 -->
- <N> 行，用途：<排版理由>。超過 30 行時這裡要改成元件缺口提報，不是照貼。

## 狀態 + 互動（AI 推測）
- 頁面級：整頁 loading / 空狀態 / 權限 — ⚠️ 待確認
- 欄位級：<input error / disabled / loading / 送出後；點擊 / 驗證觸發> — ⚠️ 待確認

## 待確認新件
<!-- 缺件門檻第 3 層：真缺件、PM 同意的新元件 -->
- <新提議元件> — 🆕 待確認

## Prototype
- 路徑 `prototypes/<project>/<pages|fragments>/<name>.blade.php`
- 預覽 `http://localhost:8100/prototypes/<project>/<name>`
- 起動：`cd preview && docker compose up -d && npm run dev`

## 套用方式
<!-- fragment -->
    php scripts/apply.php <project> <name> --output=patch \
        --target=<你的專案 clone>/resources/views/<...>.blade.php

腳本會移除 `@piFragment` 與 `@piFixture`、依 slot marker 縮排對齊，產出 unified
diff（不會直接改你的檔）。`git apply` 後只需把資料改由 controller 傳入。

專案端要先在插入位置埋 **HTML 註解**（不是 blade 註解）：

    <!-- @pi-slot: <slot-name> -->

<!-- page -->
    php scripts/apply.php <project> <name>          # 印出可貼的 blade

只動兩處：刪掉 `@piFixture` 那幾行、`@extends` 換成專案 layout。
`@section('content')` 內的 body 一個字都不用改。
```

## 後端 handoff 模板

必列：資料契約（指向 fixture）、選項/資料來源、送出目標/API 意圖、驗證規則。

```markdown
# [頁面名] — 後端 Handoff

## 資料契約
Fixture 檔就是契約 —— controller 傳出同樣結構即可，前端 blade 不用改：

| Fixture | 對應變數 | 結構 |
|---------|---------|------|
| `prototypes/<project>/fixtures/member-status.php` | `$statuses` | `[['value' => string, 'label' => string], …]` |

<!-- 不要另外手寫一份欄位表 —— 會與 fixture 不同步。型別待補的地方直接在
     fixture 檔加註解，或在下方「型別待補」列出 -->

## 型別待補
| 欄位 | 目前 fixture 值 | 實際型別 |
|------|----------------|---------|
| joinedAt | `'2026-03-14'`（字串） | date? datetime? — 🔧 待補 |

## 選項 / 資料來源
- select「<名稱>」選項來源：? — 🔧 待補

## 送出目標 / API 意圖
- 表單送出目標 endpoint / method：? — ⚠️ 待確認

## 驗證規則
| 欄位 | 規則 |
|------|------|
| email | 必填、email 格式 — 🔧 待補 |
```

## 三處耦合（別重做）

- 前端「版面結構 + 元件清單」= 第 1 步給 PM 看的回述摘要，同一結構。
- 後端「資料契約」= `fixtures/` 的實際內容，指向檔案而非複製一份。
- 擱置的新件（PM 審批選「擱置」）：兩份 handoff 底部各列一行「擱置項」，不進正式清單。

## 已驗證範例輸出（回歸基準）

`.scratch/salary-report/frontend-handoff.md` 與 `backend-handoff.md` —— 照本模板
產出的完整範例（薪資回報表單，page prototype）。對應的 prototype 是
`prototypes/project-a/pages/salary-report.blade.php`。

`.scratch/prototype-handoff/example/` 與 `.scratch/pm-to-preview-test/` 底下的
是 **blade 化之前的舊路線** 產物（產已廢除的 `preview-static/*.html`、元件清單列 `gl_`
class），檔案開頭已標記。結構可參考，**不要照抄**。
