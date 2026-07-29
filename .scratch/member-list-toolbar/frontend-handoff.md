# 會員列表工具列（日期範圍 + 匯出）— 前端 Handoff

## 版面結構

一列，插在會員列表頁的表格上方（既有 `member-list.filters` 篩選列的下方）。

```
[起始日 ▾] [結束日 ▾]                              [⬇ 匯出]
└─ 靠左，兩欄並排，各 168px                        └─ 靠右
```

`justify-content: space-between` —— 日期靠左、匯出靠右。

## 元件清單

| 實例 | Pi DS 元件 | props |
|------|-----------|-------|
| 起始日 | `<x-pi::form-control>` | `type="date" id="dateFrom" name="dateFrom"` |
| 結束日 | `<x-pi::form-control>` | `type="date" id="dateTo" name="dateTo"` |
| 匯出鈕 | `<x-pi::button>` | `variant="outline" tone="basic" icon-left="icon-download"` |

匯出鈕用 outline 而非 solid：它是次要動作，主要動作是篩選列的「篩選」鈕。

## 歧義收斂

- **「日期範圍」→ 兩個原生 date input，不是單欄式範圍選擇器。**
  設計系統沒有任何日期元件（`resources/views/components/` 無 date / calendar / picker，`assets/symicon.css` 也沒有 `icon-calendar`，只有 `icon-clock*`）。單欄式範圍選擇器需要 JS 日曆彈窗，屬真缺件。**PM 已確認採兩欄式。**
  → 日後若需要「最近 7 天 / 本月」這類快捷選項，再走缺件流程做正式的 `date-range` 元件。

- **這一列獨立成新 slot `member-list.toolbar`，沒有併進 `member-list.filters`。**
  匯出是「動作」、篩選是「查詢條件」，混成一列五個元素過擠，且日後改動互相干擾。**PM 已確認。**

## ⚠️ 原生 date input 的外觀差異

`<input type="date">` 的日曆圖示與彈窗由瀏覽器提供，Chrome / Safari / Firefox 長得不一樣，設計系統無法統一。`.gl_form-control` 只負責框線、高度、字級。

如果驗收要求三個瀏覽器完全一致 → 那就是 `date-range` 元件缺口，需要重新提報。⚠️ 待確認。

## 元件缺口（已提報，不重複）

- **`field-label`** —— 欄位標籤沒有元件。本片段又重複了兩次（累計第四次），沿用 `salary-report` 的疊法（`fz-title-sm fz-tit` + `<label for>` 綁 `id` prop）。
  完整 4 欄提議見 `.scratch/salary-report/frontend-handoff.md`。**這次的重複強化了該提議的優先度。**

## Page-scoped SCSS

11 行。除了三個排版 class，多一條**覆寫 DS 元件寬度**的規則：

```scss
/* .gl_form-group 是 width:100%，工具列裡要收成固定寬才不會把匯出鈕推掉 */
.pa-toolbar__field .gl_form-group { width: 168px; }
```

⚠️ 待確認：若 `form-control` 日後加上尺寸 prop（例如 `width="sm"`），這條就可以刪掉。目前設計系統沒有這個 prop。

未超過 `CLAUDE.md` 第 3 條的 30 行上限。

## 狀態 + 互動（AI 推測）

- 頁面級：
  - 匯出中的 loading（按鈕換 `<x-pi::loading>` + `disabled`）— ⚠️ 待確認
  - 匯出失敗的錯誤呈現（`<x-pi::alert tone="danger">`？）— ⚠️ 待確認
  - 匯出完成是直接下載檔案，還是寄 email／背景任務 — ⚠️ 待確認（影響要不要 loading）
- 欄位級：
  - 起始日晚於結束日時的錯誤呈現（可用 `state="invalid" feedback="…"`）— ⚠️ 待確認
  - 日期改變時是否即時重新查詢，還是要按「篩選」— ⚠️ 待確認

## Prototype

- 路徑 `prototypes/project-a/fragments/member-list.toolbar.blade.php`
- 預覽 `http://localhost:8100/prototypes/project-a/member-list.toolbar`
- 起動：`cd preview && docker compose up -d && npm run dev`

> preview 一次只注入一個 fragment。這頁看到的是 toolbar 坐在宿主裡，
> `member-list.filters` 那一列不會同時顯示。

## 套用方式

**專案端要先埋 marker**（HTML 註解，不是 blade 註解），位置在既有 filters marker 的下方、表格上方：

```blade
<!-- @pi-slot: member-list.filters -->

<!-- @pi-slot: member-list.toolbar -->     ← 新增這一行
```

埋好後產 patch：

```bash
php scripts/apply.php project-a member-list.toolbar --output=patch \
    --target=<你的專案 clone>/resources/views/members/index.blade.php > toolbar.patch

cd <你的專案> && git apply toolbar.patch
```

腳本會移除 `@piFragment` 與 `@piFixture`、依 marker 縮排對齊。`git apply` 之後只需把 `$toolbar` 改由 controller 傳入。
