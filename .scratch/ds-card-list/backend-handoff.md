# ds-card-list（面試邀約）— 後端 Handoff

專案：`interview`　類型：page prototype
Prototype：`prototypes/interview/pages/ds-card-list.blade.php`

## 資料契約

Fixture 檔就是契約 —— controller 傳出同樣結構即可，前端 blade 不用改：

| Fixture | 對應變數 | 結構 |
|---|---|---|
| `prototypes/interview/fixtures/ds-card-list.php` | `$cards` | `[['id' => int, 'title' => string, 'excerpt' => string, 'image' => string, 'imageAlt' => string, 'action' => ['label' => string, 'href' => string]], …]` |
| `prototypes/interview/fixtures/ds-card-list-nav.php` | `$navLinks` | `[['label' => string, 'href' => string, 'icon' => string, 'current' => bool], …]` |

`$cards` 的 `excerpt` 前端會截到 2 行、`title` 截到 1 行（CSS 截斷，非後端裁字）—— 不需要在 API 端先裁，但字太長會被切掉，長度建議由內容端把握。

## 型別待補

| 欄位 | 目前 fixture 值 | 實際型別 |
|---|---|---|
| `cards[].id` | `101`（int） | 面試邀約的 PK？還是職缺 PK？ — 🔧 待補 |
| `cards[].image` | data URI 的灰塊 SVG（佔位） | 正式應為圖片 URL（絕對還是相對？有無多尺寸／CDN 變體？）— 🔧 待補 |
| `cards[].imageAlt` | 字串 | 由後端給還是前端用 title 組？ — 🔧 待補 |
| `cards[].action.href` | `'#'` | route name + 參數，還是完整 URL？ — 🔧 待補 |
| `cards[].action.label` | `'查看細節'` | 固定文案還是隨狀態變（例：待回覆 / 已排定）？ — ⚠️ 待確認 |
| `navLinks[].href` | `'#'` | 同上 — 🔧 待補 |
| `navLinks[].icon` | `'icon-home'` 等 symicon class | 寫死在 view 還是後端給？建議寫死在 view（純視覺）— ⚠️ 待確認 |
| `navLinks[].current` | `bool` | 建議由 route name 比對產生，不要存 DB — ⚠️ 待確認 |

`cards[].image` 的佔位圖在 fixture 內用 `base64_encode()` 現算，理由寫在該檔註解：prototype 不依賴外部圖床（圖掛掉時 PM 會誤以為是版面壞了）。

## 選項 / 資料來源

- `$cards` 來源：哪個 model / 哪些條件（只列「已排定」的面試？含已結束的？）— 🔧 待補
- `$cards` 排序：面試時間？建立時間？— 🔧 待補
- `$cards` 是否分頁：目前 fixture 固定 3 筆，未接 `<x-pi::pagination>` — ⚠️ 待確認
- `$navLinks` 來源：建議寫死在 view / config（純導覽結構），不進 DB。若有權限控制才需後端過濾 — ⚠️ 待確認

## 送出目標 / API 意圖

本頁**沒有表單送出** —— 只有 GET 呈現 + 卡片按鈕的連結跳轉。

- 卡片按鈕的目標頁：面試細節頁？— 🔧 待補
- 導覽連結的目標頁：5 條 route 各對到哪 — 🔧 待補

## 驗證規則

無使用者輸入 → 無驗證規則。

## 新件決策對後端的影響

`nav-link`（sidebar 當前頁高亮）**PM 已決定不由設計系統提供，各專案自行實作**
→ 所以 `navLinks[].current` 的型別由 interview 端自己定，不會再因設計系統改版而變動。
目前假設 `bool`，若 interview 要做多層導覽（父項展開）再自行擴充結構。

`card` 同樣不立案（各專案自行改樣式），純視覺，不進後端。
