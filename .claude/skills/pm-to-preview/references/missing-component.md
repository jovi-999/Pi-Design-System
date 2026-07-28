# 缺件流程：三層門檻 → 提議 → 審批 → 標記

需求元件在 Pi DS 找不到現成件時走這條。component 與 token 共用同一 loop，提議文字註明類別。

## 先查已確認的缺口

`CLAUDE.md` 底部的「已知元件缺口」段落（自動生成）列的是**已經確認缺、不要重複提報**的東西。目前有兩個：

- `dropdown` 只有選單項目（`<x-pi::dropdown-item>`），**沒有浮出面板**（定位／陰影／開關行為）
- `modal` 只有面板外觀（`<x-pi::modal>`），**沒有遮罩／置中／focus trap**

命中這兩個直接走第 3 層的 placeholder 畫法並在 handoff 標記，不用再跑提議 loop（已經提報過了）。

## 三層門檻（判斷是否真缺件）

1. **能用現成 `<x-pi::*>` 元件直接命中** → 直接用，不提議。
   判斷前先讀該元件的 `resources/views/components/<name>.meta.php` —— props 值域各元件不同，給錯值元件會丟例外。
2. **不能，但用現有 token（border / shadow / spacing / color / radius / type）加一層 wrapper 疊得出視覺** → **組合件**：prototype 直接跑，handoff 標 `⚠️ 待確認` 並註明「組合而非正式元件」，**不進提議 loop**。
   排版用的 `<style>` 計入 `CLAUDE.md` 第 3 條的 30 行上限；超過就升級成第 3 層。
3. **兩者皆不能**（需 JS 行為或全新視覺 primitive，例：日曆彈窗、可排序表格、浮出面板）→ **真缺件**，進提議 loop。

> 缺 token 同走此門檻：幾乎總能用「最接近的現有階」頂替；真需新 token 才進提議。

## 提議內容（AI → PM，固定 4 欄）

1. 暫定名稱 + 用途一句（例：`date-picker`：選單一日期）
2. 最接近的現有件（錨定，例：類似 `dropdown` 展開 + 日曆格線）
3. 為何組不出一句（觸發第三層的理由）
4. prototype 先用什麼頂替

**不含**：完整視覺設計、SCSS、token 值 —— 那是定案後前端的事。token 類提議同 4 欄，第 1 欄註明「新 token」而非新元件。

## PM 審批（固定 3 種結果）

1. **同意（新件）** → 定案為待做新件；prototype 用 placeholder，handoff 標 `🆕 待確認`。
2. **否決 → 改用現有** → PM 指定現有件替代 → 退回門檻 1/2，preview 直接用該件，不再是缺件。
3. **擱置** → prototype 留空位 + 一句 TODO，不進 handoff 正式清單，記在兩份 handoff 底部「擱置項」。

**無回應時 AI 不自作主張，停等 PM。**

## prototype placeholder（可跑 blade，零自創）

- 用最接近的現有 `<x-pi::*>` 當殼。
- 外層包一個 `class="gl_border-outer"` 的 div（現有 solid 描邊）框出範圍。
- 可見文字標籤「🆕 xxx（待確認）」+ blade 註解「暫用 X 頂替，正式件待前端做」。
- **無 dashed border token**（查 `resources/scss/` 已確認）；禁自創，故用現有 solid `gl_border-outer`。

```blade
{{-- 🆕 date-picker 待確認：暫用 form-control 頂替，正式件待前端做 --}}
<div class="gl_border-outer" style="padding: 8px;">
    <div class="fz-body-xs" style="color: var(--fg-3);">🆕 date-picker（待確認）</div>
    <x-pi::form-control name="joined_at" placeholder="YYYY-MM-DD" />
</div>
```

## handoff 呈現（承接標記約定）

- **前端 handoff**「待確認新件」段：列每個同意新件的完整 4 欄 + 「正式 class 待設計」，標 `🆕 待確認`。
- **後端 handoff**：新件含資料意涵才在「資料欄位清單」列該欄，備註 `🆕 待確認`（元件未定案 → 型別連帶未定）；純視覺新件不進後端。
- **擱置項**：兩份 handoff 底部各列一行。
