# CLAUDE.md — 專案規則（每次對話自動載入）

## 語言
- 回覆**一律使用繁體中文**，禁止簡體字。

## 變數 / Token 規則（最高優先）
- **嚴格禁止自創任何變數、token、class 內容。** 只能使用設計系統中**實際存在**的 token / class。
- 不確定某個 token / class 是否存在時，先 `grep` 或讀 `src/tokens/` 、`src/components/` 確認，**絕不憑記憶或推測發明**。
- 需要新的值時，先問使用者、或請使用者提供來源（Figma、原始 SCSS），不要自己編一個。
- 反例（過去發生過的錯誤）：自創 `$shadow-ring` —— 設計系統並沒有這個變數。「Ring」是 `.gl_shadow-*` 疊加 `.gl_border-outer` / `.gl_border-inner` 的組合。

## 修改範圍
- 使用者要求小修改時，只動該處，不要順手「改善」其他部分。
- 改 token / class 命名屬於 breaking change，需同步更新所有引用處與文件（README、SKILL.md、preview）。

## Agent skills

### Issue tracker

GitHub Issues via the `gh` CLI（repo：`jovi-999/Pi-Design-System-`）。See `docs/agents/issue-tracker.md`.

### Triage labels

五個 canonical 預設 label（label 字串 = 角色名）。See `docs/agents/triage-labels.md`.

### Domain docs

Single-context — 根目錄 `CONTEXT.md` + `docs/adr/`。See `docs/agents/domain.md`.
