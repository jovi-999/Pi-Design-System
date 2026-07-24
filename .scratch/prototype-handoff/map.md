<!-- wayfinder:map -->
# Prototype 討論 → 交棒流程

## Destination

從 PM 的文字／口述描述，AI agent 依 Pi Design System 的 guideline 產出：
1. 一個**可跑的 HTML preview** prototype（只用現有 Pi DS component / token；缺件時提議新件並標記待確認），以及
2. 一份同步的**結構化描述 + 交棒規格**，可接力給前後端執行。

本次終點：把上述**流程跑通並定下 handoff 規格格式**，以一個具體範例驗證，而非固化成可重用 skill。

## Notes

- 領域：Pi Design System（SCSS component + token + preview）。
- **變數/token 鐵則**：只能用 `src/tokens/`、`src/components/` 實際存在的 token/class，禁自創（見 `CLAUDE.md`）。缺件走「AI 提議 → PM 同意 → 標記待確認」流程。
- 每個 ticket 用 `/grilling` + `/domain-modeling`；research 用 `/research`；prototype 用 `/prototype`。
- 回覆繁體中文。

## Decisions so far

<!-- 一行一個已關閉 ticket；細節在該 ticket，這裡只給 gist + 連結 -->

- [03 現有 preview 機制盤點](issues/03-preview-mechanism.md) — prototype HTML 放 `preview/`，`npm run dev`（vite:5173）跑；照抄 `preview/button.html` 範本，引 `/src/index.scss`；新元件頁需在 `preview/index.html` PAGES + `src/components/index.scss` 兩處註冊。
- [01 輸入格式](issues/01-input-format.md) — 採「自由文字輸入 + AI 回述結構化摘要確認」（非填表）；摘要只含元件 + 版面兩類給 PM 確認，狀態/資料行為標「待前後端補」不問 PM。回述結構與 `02` 輸出欄位共用。
- [02 Handoff 規格格式](issues/02-handoff-spec-format.md) — 前端/後端**兩份分開 markdown**。前端：版面+元件class+狀態互動(⚠️待確認)+preview+🆕待確認新件；後端：資料欄位+選項來源+送出目標+驗證規則(🔧待補)。標記約定 `⚠️待確認`/`🔧待補`/`🆕待確認` 三種。含兩份模板。
- [04 缺件流程](issues/04-missing-component-flow.md) — **三層門檻**（現成命中／現有 token 疊得出=組合件走⚠️不提議／皆不能=真缺件進 loop；component 與 token 共用）→ **提議 4 欄**（名+用途/最近現有件/為何組不出/頂替物）→ **PM 審批 3 種**（同意新件／否決改現有／擱置，無回應停等）→ **prototype placeholder**（最近現有件殼 + `gl_border-outer` 框 + 文字標籤「🆕（待確認）」+ 內嵌註解；無 dashed token 禁自創）→ **handoff**（前端「待確認新件」段列全 4 欄；後端有資料意涵才列欄帶🆕；擱置項兩份底部）。
- [05 端到端範例驗證](issues/05-end-to-end-example.md) — 「navbar + 表單」範例跑通全流程：命中件直接用、選單歧義→原生 `select`、navbar→組合件(⚠️)、產出 `preview/example-signup.html`(200 無 error) + 前後端兩份 handoff，零自創。暴露 3 個「模板可加強」缺口（歧義收斂無專欄／組合件無獨立段／缺頁面級狀態），不阻塞。**→ 終點達成**。

## Not yet specified

<!-- 空 —— 05 收尾，路徑走完，無剩餘決策 -->
- （無）

## Out of scope

- 把整條流程固化成可重用 skill / guideline 文件 —— 本次終點是驗證流程 + 定格式，skill 化另開 effort。
- 前後端實作本身（handoff 之後的任務）。
