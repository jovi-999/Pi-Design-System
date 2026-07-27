// Build smoke test —— 確認 sass build 後產出 css 有預期內容與正確分層
import fs from "node:fs";

const css = fs.readFileSync("dist/pi-ds.css", "utf8");

let pass = 0, fail = 0;
const check = (label, ok, detail = "") => {
  console.log(`  ${ok ? "✓" : "✗"}  ${label.padEnd(26)} ${detail}`);
  ok ? pass++ : fail++;
};

// ---------- 1. 內容存在性 ----------
const expectations = [
  ["green-500 token",    "--cl-green-500"],
  ["red-500 token",      "--cl-red-500"],
  ["semantic alias",     "--brand"],
  ["button class",       ".gl_btn"],
  ["modal class",        ".gl_modal"],
  ["alert class",        ".gl_alert"],
  ["body fz class",      ".fz-body-md"],
  ["legacy h1 alias",    ".fz-h1"],
];
for (const [label, needle] of expectations) check(label, css.includes(needle), needle);

// ---------- 2. @layer 順序宣告必須在最前面 ----------
// CSS 的 @layer 順序由第一次出現決定，所以順序宣告必須先於任何 @layer 區塊，
// 否則層級關係會隨載入順序漂移（見 resources/scss/_layers.scss）。
const orderIdx = css.indexOf("@layer pi-reset, pi;");
const firstBlockIdx = css.search(/@layer\s+[\w-]+\s*\{/);
check(
  "@layer 順序宣告存在",
  orderIdx !== -1,
  "@layer pi-reset, pi;",
);
check(
  "順序宣告在所有 layer 區塊之前",
  orderIdx !== -1 && (firstBlockIdx === -1 || orderIdx < firstBlockIdx),
  `宣告 @${orderIdx} < 首個區塊 @${firstBlockIdx}`,
);

// ---------- 3. 不得有未分層規則 ----------
// index.scss（不含 reset）的產物內，每一條規則都必須在某個 @layer 內。
// 漏包的規則會變成未分層 —— 未分層優先權高於任何分層樣式，會反過來蓋掉
// 專案的既有樣式，樣式隔離失效。
const unlayered = [];
{
  let depth = 0, inLayer = false, layerDepth = 0;
  css.split("\n").forEach((line, i) => {
    const t = line.trim();
    // 選擇器行：以 . : [ # * 或英數開頭且含 {，且非 at-rule
    if (!inLayer && !t.startsWith("@") && /^[*.:[#a-zA-Z][^{]*\{/.test(t)) {
      unlayered.push(`${i + 1}: ${t.replace(/\s*\{.*$/, "")}`);
    }
    if (/^@layer\s+[\w-]+\s*\{/.test(t)) { inLayer = true; layerDepth = depth; }
    depth += (t.match(/\{/g) || []).length - (t.match(/\}/g) || []).length;
    if (inLayer && depth <= layerDepth) inLayer = false;
  });
}
check(
  "無未分層規則",
  unlayered.length === 0,
  unlayered.length ? `${unlayered.length} 條: ${unlayered.slice(0, 3).join(" / ")}` : "0 條",
);

// ---------- 4. reset 不得混進 index.scss 產物 ----------
// reset 是頁面級意見，必須是獨立檔由使用端自行決定要不要載。
check(
  "index 產物不含 reset",
  !/@layer pi-reset\s*\{/.test(css),
  "無 @layer pi-reset 區塊",
);

// ---------- 5. 元件契約必須在產物內 ----------
// corner-shape / font-weight 沒有任何標準 reset 會提供，元件靠它才長得對。
check("元件契約：corner-shape", css.includes("corner-shape"), "");
check("元件契約：font-weight 基準", /:where\([^)]*gl_[^)]*\)/.test(css), ":where(…[class*=gl_]…)");

const sizeKb = (css.length / 1024).toFixed(1);
console.log(`\nResult: ${pass}/${pass + fail} checks passed · ${sizeKb} KB`);

if (fail > 0) process.exit(1);
