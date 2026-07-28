# 回報薪資 — 前端 Handoff

> 這份是 blade 路線的第一份 handoff，同時是 `pm-to-preview` skill 改版後的端到端驗證輸出。

## 版面結構

單欄，最大寬 560px，置中。

```
標題「回報薪資」
callout（匿名處理說明）
欄位 · 職稱          （標籤 + input）
欄位 · 產業別        （標籤 + select）
欄位 · 月薪          （標籤 + input + 說明文字）
欄位 · 補充說明      （標籤 + textarea）
一列 · toggle + 說明文字
行動列 · 送出 / 取消（左對齊並排）
```

## 元件清單

| 實例 | Pi DS 元件 | props |
|------|-----------|-------|
| 匿名說明 | `<x-pi::callout>` | `tone="success" icon="icon-shield-check" title="你的資料會匿名處理"` |
| 職稱 | `<x-pi::form-control>` | `id="jobTitle" name="jobTitle" placeholder="例：資深後端工程師"` |
| 產業別 | `<x-pi::form-control>` | `id="industry" type="select" name="industry" placeholder="請選擇產業" :options="$industries"` |
| 月薪 | `<x-pi::form-control>` | `id="monthlySalary" name="monthlySalary" placeholder="50000" prompt="以新台幣計算，稅前月薪"` |
| 補充說明 | `<x-pi::form-control>` | `id="note" type="textarea" name="note"` |
| 公開統計開關 | `<x-pi::toggle>` | `id="publicProfile" size="sm" tone="success" name="publicProfile"` |
| 送出鈕 | `<x-pi::button>` | `tone="success" icon-left="icon-checked"` |
| 取消鈕 | `<x-pi::button>` | `variant="gray" tone="basic"` |

## 歧義收斂

- 「產業別選單」→ `<x-pi::form-control type="select">`（非 `<x-pi::dropdown-item>` 浮出選單）— 表單單選情境。
- 「是否公開」→ `<x-pi::toggle>`（非 `<x-pi::checkbox>`）— PM 描述是「開關」，且此設定即時生效語意較接近 toggle。⚠️ 待確認：若送出後才生效，改用 checkbox 更合語意。

## 待確認新件（🆕）

### `field-label` — 表單欄位標籤

設計系統**沒有欄位標籤元件**（已 grep `resources/scss/components/_form.scss`，內無任何 label 相關 class）。本頁同一個標籤結構重複四次，超出「一次性組合件」的範圍，因此以正式缺口提報。

| 提議欄位 | 內容 |
|---|---|
| 暫定名稱 + 用途 | `field-label`：表單欄位的標籤，可帶必填標記 |
| 最接近的現有件 | 無正式件。目前用 `fz-title-sm fz-tit` + 6px 下間距疊出 |
| 為何組不出 | 視覺疊得出來，但**沒有元件就沒有結構契約**：`for`/`id` 綁定、必填標記、與 `form-control` 的間距，每個頁面都得自己重做一次，四個欄位就重複四次 |
| prototype 先用什麼頂替 | `<label class="pa-field__label fz-title-sm fz-tit" for="...">`，id 由 `form-control` 的 `id` prop 綁定 |

🆕 待確認。**正式 class 待設計。**

> 附帶已修的元件缺口：`form-control` 與 `toggle` 原本無法被外部 `<label for>` 關聯 —— `id` 若走 `$attributes` 會落在外層 `div` / `label` 上。兩支已新增 `id` prop（落在真正的 control 上），本頁的 5 個 `for` 都已實際綁定。

## Page-scoped SCSS

6 行（`.pa-form` / `.pa-field` / `.pa-field__label` / `.pa-field--gap` / `.pa-row` / `.pa-actions`），用途：單欄表單的寬度、欄位間距、行動列並排。未超過 `CLAUDE.md` 第 3 條的 30 行上限。行數也會顯示在 `/prototypes` 清單頁。

> 刻意不用 inline style 調間距（例如 `<div style="height: 24px">`）：`PrototypeCatalog::countInlineStyleLines()` 只數 `<style>` 區塊，用 inline style 會讓自訂樣式量繞過 30 行門檻的量測，缺口就被藏起來了。

## 狀態 + 互動（AI 推測）

- 頁面級：送出中（按鈕換 `<x-pi::loading>` + `disabled`）／送出成功導向何處／未登入是否可填 — ⚠️ 待確認
- 欄位級：
  - 月薪的數字格式驗證與錯誤呈現（用 `state="invalid" feedback="…"`）— ⚠️ 待確認
  - 職稱／產業別是否必填 — ⚠️ 待確認
  - 「取消」的行為（返回上一頁？清空表單？）— ⚠️ 待確認

## Prototype

- 路徑 `prototypes/project-a/pages/salary-report.blade.php`
- 預覽 `http://localhost:8100/prototypes/project-a/salary-report`
- 起動：`cd preview && docker compose up -d && npm run dev`

## 套用方式

這是 **page** prototype：

```bash
php scripts/apply.php project-a salary-report
```

輸出可直接貼進專案的 blade。只動兩處：

1. 刪掉開頭兩行 `@piFixture` — 資料改由 controller 傳入
2. `@extends('pi::layouts.preview')` 換成專案自己的 layout

`@section('content')` 內的 body 一個字都不用改 —— 專案與 preview 吃同一版元件套件。

前置條件：專案要先 `composer require company/pi-design-system` 並在 `app.scss` `@use` 套件的 SCSS（見 `design-guideline-spec.md` 的 Phase 4 / 6.1）。
