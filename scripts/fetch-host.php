<?php

/**
 * 抓專案頁面的 rendered HTML，存成 prototypes/<project>/_hosts/<name>.html。
 *
 * 目的：讓 PM 看到的 fragment 是坐在真實脈絡裡的樣子 —— 能判斷跟上方標題的
 * 間距、跟下方表格的視覺重量搭不搭，而不是懸在空白頁上。
 *
 * 用法：
 *   php scripts/fetch-host.php <project> <name> <url>
 *
 * 例：
 *   php scripts/fetch-host.php project-a members-index https://staging.project-a.example.com/members
 *
 * 刻意不下載 CSS：把 <link> 的相對路徑改寫成目標站的絕對 URL，讓快照的樣式
 * 跟著專案走。專案改樣式時快照會自動反映，不會變成一份凍結的舊畫面。
 */

declare(strict_types=1);

$root = dirname(__DIR__);

[$script, $project, $name, $url] = array_pad($argv, 4, null);

if ($project === null || $name === null || $url === null) {
    fwrite(STDERR, <<<TXT
    用法：php scripts/fetch-host.php <project> <name> <url>

      project  prototypes/ 底下的專案目錄名，如 project-a
      name     存檔名（不含 .html），如 members-index
      url      要抓的頁面完整 URL（通常是 staging）

    TXT);
    exit(1);
}

if (! preg_match('/^[a-z0-9\-]+$/', $project) || ! preg_match('/^[a-z0-9\-]+$/', $name)) {
    fwrite(STDERR, "project 與 name 只允許小寫英數與 dash\n");
    exit(1);
}

if (! filter_var($url, FILTER_VALIDATE_URL)) {
    fwrite(STDERR, "URL 格式不正確：{$url}\n");
    exit(1);
}

// ---------- 抓取 ----------

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 20,
        'follow_location' => 1,
        'max_redirects' => 5,
        // 有些 staging 會依 UA 回不同內容；明確標示來源方便對方查 log
        'header' => "User-Agent: Pi-DS-fetch-host/1.0\r\n",
        'ignore_errors' => true, // 4xx/5xx 也要拿到 body 才能講清楚錯在哪
    ],
]);

$html = @file_get_contents($url, false, $context);

if ($html === false) {
    fwrite(STDERR, "抓不到 {$url}（連線失敗或逾時）。若目標需要登入，請改用瀏覽器另存 HTML 後手動放進 _hosts/。\n");
    exit(1);
}

$status = 0;
foreach ($http_response_header ?? [] as $line) {
    if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
        $status = (int) $m[1];
    }
}

if ($status >= 400) {
    fwrite(STDERR, "目標回應 HTTP {$status}。快照未寫入。\n");
    fwrite(STDERR, "若是 302 到登入頁，代表這頁需要 session —— 改用瀏覽器另存 HTML。\n");
    exit(1);
}

// ---------- 改寫資源路徑 ----------

$parsed = parse_url($url);
$origin = $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

// 只改寫 href/src 的「站根相對路徑」（/foo）→ 絕對 URL。
// 不動已經是絕對 URL 的，也不動 data: 與 #anchor。
$rewritten = preg_replace(
    '#\b(href|src)=(["\'])/(?!/)#',
    '$1=$2' . $origin . '/',
    $html
);

$slotCount = preg_match_all('/<!--\s*@pi-slot:\s*([\w.\-]+)\s*-->/', $rewritten, $slotMatches);

// ---------- 寫檔 ----------

$dir = "{$root}/prototypes/{$project}/_hosts";

if (! is_dir($dir) && ! mkdir($dir, 0o755, true) && ! is_dir($dir)) {
    fwrite(STDERR, "建不出目錄 {$dir}\n");
    exit(1);
}

$banner = <<<HTML
<!--
    宿主快照 —— 由 scripts/fetch-host.php 自動抓取，請勿手改。

    來源：{$url}
    抓取時間：由 git commit 時間為準（此檔刻意不寫時間戳，避免每次重抓都產生 diff）

    ⚠️ 快照會過期。專案改版後重跑 fetch-host.php，不要手動編輯這個檔。
-->

HTML;

$target = "{$dir}/{$name}.html";
file_put_contents($target, $banner . $rewritten);

// ---------- 回報 ----------

echo "已寫入 prototypes/{$project}/_hosts/{$name}.html（" . number_format(strlen($rewritten)) . " bytes）\n";

if ($slotCount === 0) {
    fwrite(STDERR, <<<TXT

    ⚠️  快照裡找不到任何 <!-- @pi-slot: … --> 錨點。

        fragment 將無法注入，preview 只會顯示裸片段。

        專案端要在目標位置埋一行 **HTML 註解**（不是 blade 註解）：

            <!-- @pi-slot: member-list.filters -->

        用 blade 註解（{{-- --}}）的話 render 後就消失了，快照裡不會有錨點。

    TXT);
    exit(1);
}

echo "找到 " . $slotCount . " 個 slot 錨點：" . implode(', ', $slotMatches[1]) . "\n";
