# 回報薪資 — 後端 Handoff

## 資料契約

Fixture 檔就是契約 —— controller 傳出同樣結構即可，前端 blade 不用改：

| Fixture | 對應變數 | 結構 |
|---------|---------|------|
| `prototypes/project-a/fixtures/salary-industries.php` | `$industries` | `[['value' => string, 'label' => string], …]` |
| `prototypes/project-a/fixtures/salary-report-form.php` | `$form` | `['jobTitle' => string, 'industry' => ?string, 'monthlySalary' => string, 'note' => string, 'publicProfile' => bool]` |

`$form` 是表單預填值。新建情境全部給空值即可；編輯情境帶入既有資料。

## 型別待補

| 欄位 | 目前 fixture 值 | 實際型別 |
|------|----------------|---------|
| `monthlySalary` | `''`（字串） | int？decimal？前端 input 是 text，送出會是字串 — 🔧 待補 |
| `industry` | `null` | 是傳 value 字串還是產業 id（int）？— 🔧 待補 |
| `publicProfile` | `true` | bool。注意 HTML checkbox 未勾選時**不會送出該欄位**，後端要自行 default false — ⚠️ 待確認 |

## 選項 / 資料來源

- select「產業別」選項來源：目前是 fixture 的 4 筆假資料。實際來源是資料表、設定檔、還是列舉常數？— 🔧 待補
- 產業別是否會增減？若會，前端不需改動（`:options` 吃陣列）。

## 送出目標 / API 意圖

- 表單送出目標 endpoint / method：？— ⚠️ 待確認
- 是否需要防重複送出（同一使用者同一公司只能回報一次）？— ⚠️ 待確認
- 匿名處理的實作位置：前端 handoff 的 callout 對使用者承諾「公司看不到姓名與 Email」，這需要後端在儲存或查詢時實際隔離 — ⚠️ 待確認由誰保證

## 驗證規則

| 欄位 | 規則 |
|------|------|
| `jobTitle` | 必填？長度上限？— 🔧 待補 |
| `industry` | 必填、需在選項值域內 — 🔧 待補 |
| `monthlySalary` | 必填、數字、合理範圍（前端有「請確認金額是否合理」的 prompt 樣式可用）— 🔧 待補 |
| `note` | 選填、長度上限 — 🔧 待補 |

驗證失敗時的回饋位置已在前端預留：`<x-pi::form-control state="invalid" feedback="訊息">`。
