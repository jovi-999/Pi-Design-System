# 變數 / token 鐵則（最高優先，開工前先讀）

> 本檔隨 skill 資料夾一起 copy，是 `pm-to-blade` 的自帶真相源 —— **不依賴專案 CLAUDE.md**，多專案共用同一份、複製不遺漏。

## 鐵則

- **嚴格禁止自創任何 DS 變數 / token / class 內容。** 只能用 vendored Pi Design System 中**實際存在**的 token / class。
- 不確定某個 token / class 是否存在時，先 `grep` 或讀 `resources/sass/pi-ds/tokens/`、`resources/sass/pi-ds/components/` 確認，**絕不憑記憶或推測發明**。
- 需要新的值時，先問使用者、或請使用者提供來源（Figma、原始 SCSS），不要自己編一個。

## 什麼算「自創」（禁止）／什麼不算（允許）

| 行為 | 判定 |
|---|---|
| 新增一個 DS token 值（如自訂 `--shadow-ring`、新色階）| ❌ 自創，禁止 |
| 捏一個不存在的 `gl_*` class（如 `gl_carousel`）當它已存在 | ❌ 自創，禁止 |
| 用現有 `gl_*` class（命中件）| ✅ 允許 |
| **組合件**：prototype-scoped class（如 `.proto-x-banner`），值**全部來自現有 token**（`@use 'pi-ds/tokens'` 的 `--cl-*` / `--radius-*` / `--fg` …）| ✅ 允許（這是「組合」，非自創 DS token）|
| 真缺件 → 走提議 loop 請 PM 定案，prototype 先用現有件當 placeholder | ✅ 允許（見 missing-component.md）|

## 反例（歷史踩過的雷）

- 自創 `$shadow-ring` —— Pi DS 並沒有這個變數。「Ring」是 `.gl_shadow-*` 疊加 `.gl_border-outer` / `.gl_border-inner` 的組合，不是一個新 token。

## 一句話

**新的 class 名可以（prototype-scoped、拋棄式），新的 token 值不行。** 所有視覺數值必須追溯到一個實際存在的 Pi DS token。
