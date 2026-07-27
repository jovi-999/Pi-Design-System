# 04 缺件流程：AI 提議新 component → PM 同意 → 標記待確認

Type: grilling
Status: resolved
Blocked by:

## Question

當描述需要 Pi DS 尚未存在的 component/token 時的流程機制：AI 如何提議（提議內容含什麼）、PM 如何同意/否決、同意後「待確認」在 prototype 與 handoff 規格中如何標記，讓前後端知道這是未定案的新件。定出這個 loop 的具體步驟與標記約定。

## Answer

缺件流程 = **三層門檻 → 提議 → 審批 → 標記**。component 與 token 共用同一 loop，提議文字註明類別。

### 三層門檻（判斷是否真缺件）
1. 能用現成 component class 直接命中 → 直接用，不提議。
2. 不能，但用現有 token（border/shadow/spacing/color/radius/type）純 HTML 疊得出視覺 → **組合件**：preview 直接跑，handoff 標 `⚠️ 待確認` 並註明「組合而非正式元件」，**不進提議 loop**。
3. 兩者皆不能（需 JS 行為或全新視覺 primitive，例：日曆彈窗、可排序表格）→ **真缺件**，進提議 loop。
- 缺 token 同走此門檻：幾乎總能用「最接近的現有階」頂替；真需新 token 才進提議。

### 提議內容（AI → PM，固定 4 欄）
1. 暫定名稱 + 用途一句（例：`date-picker`：選單一日期）
2. 最接近的現有件（錨定，例：類似 `dropdown` 展開 + 日曆格線）
3. 為何組不出一句（觸發第三層的理由）
4. prototype 先用什麼頂替
- **不含**：完整視覺設計、SCSS、token 值 —— 那是定案後前端的事。
- token 類提議同 4 欄，第 1 欄註明「新 token」而非新元件。

### PM 審批（固定 3 種結果）
1. **同意（新件）** → 定案為待做新件；prototype 用 placeholder，handoff 標 `🆕 待確認`。
2. **否決 → 改用現有** → PM 指定現有件替代 → 退回門檻 1/2，preview 直接用該件，不再是缺件。
3. **擱置** → prototype 留空位 + 一句 TODO，不進 handoff 正式清單，記在兩份 handoff 底部「擱置項」。
- 無回應時 AI 不自作主張，停等 PM。

### prototype placeholder（可跑 HTML，零自創）
- 用最接近的現有件當殼（純 HTML + 現有 class）。
- 外層包 `gl_border-outer`（現有 solid 描邊）框出範圍。
- 可見文字標籤「🆕 xxx（待確認）」+ 內嵌註解「暫用 X 頂替，正式件待前端做」。
- **無 dashed border token**（查 resources/scss/ 確認）；禁自創，故用現有 solid `gl_border-outer`。

### handoff 呈現（承接 02 標記約定）
- **前端 handoff**「待確認新件」段：列每個同意新件的完整 4 欄 + 「正式 class 待設計」，標 `🆕 待確認`。
- **後端 handoff**：新件含資料意涵才在「資料欄位清單」列該欄，備註 `🆕 待確認`（元件未定案 → 型別連帶未定）；純視覺新件不進後端。
- **擱置項**：兩份 handoff 底部各列一行。
