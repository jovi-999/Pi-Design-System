<?php

/**
 * 把定案的 fragment 轉成可套進專案的內容。
 *
 * 用法：
 *   php scripts/apply.php <project> <fragment-name> [--output=patch|blade] [--target=<專案檔絕對路徑>]
 *
 * 例：
 *   php scripts/apply.php project-a member-list.filters
 *   php scripts/apply.php project-a member-list.filters --output=blade
 *   php scripts/apply.php project-a member-list.filters --target=/srv/project-a/resources/views/members/index.blade.php
 *
 * 做三件事：
 *   1. 讀 fragment 的 @piFragment manifest 取得 target / slot
 *   2. 移除 prototype 專用的行（@piFragment、@piFixture）
 *   3. 輸出 blade 片段，或（給了 --target 時）產生 unified diff patch
 *
 * 刻意不直接寫入專案檔：跨 repo 自動改檔風險太高，交出 patch 讓前端自己
 * `git apply` 並 review。
 *
 * 這支不依賴 Laravel。
 */

declare(strict_types=1);

$root = dirname(__DIR__);

// ---------- 參數 ----------

$args = array_slice($argv, 1);
$positional = [];
$options = ['output' => 'blade', 'target' => null];

foreach ($args as $arg) {
    if (preg_match('/^--([a-z-]+)=(.*)$/', $arg, $m)) {
        $options[$m[1]] = $m[2];
    } else {
        $positional[] = $arg;
    }
}

[$project, $name] = array_pad($positional, 2, null);

if ($project === null || $name === null) {
    fwrite(STDERR, <<<TXT
    用法：php scripts/apply.php <project> <fragment-name> [--output=patch|blade] [--target=<專案檔路徑>]

      --output=blade   （預設）印出可貼上的 blade 片段
      --output=patch   產生 unified diff（需要 --target 指到專案的實體檔案）
      --target=…       專案端目標檔的絕對路徑。不給時用 manifest 裡的 target 當提示

    TXT);
    exit(1);
}

$fragmentFile = "{$root}/prototypes/{$project}/fragments/{$name}.blade.php";

if (! is_file($fragmentFile)) {
    fwrite(STDERR, "找不到 fragment：{$fragmentFile}\n");
    exit(1);
}

$source = file_get_contents($fragmentFile);

if ($source === false) {
    fwrite(STDERR, "讀不到 {$fragmentFile}\n");
    exit(1);
}

// ---------- 1. 解析 manifest ----------

$manifest = [];

if (preg_match('/@piFragment\s*\(\s*\[(.*?)\]\s*\)/s', $source, $m)) {
    foreach (['target', 'slot', 'host'] as $key) {
        if (preg_match("/['\"]" . $key . "['\"]\s*=>\s*['\"](.*?)['\"]/", $m[1], $mm)) {
            $manifest[$key] = $mm[1];
        }
    }
}

if (empty($manifest['slot'])) {
    fwrite(STDERR, "fragment 沒有宣告 @piFragment 的 slot，無法決定插入位置。\n");
    exit(1);
}

// ---------- 2. 移除 prototype 專用的行 ----------

$body = $source;

// @piFragment([...])：可能跨多行
$body = preg_replace('/@piFragment\s*\(\s*\[.*?\]\s*\)\s*/s', '', $body);

// @piFixture(...)：整行刪掉。專案端資料由 controller 傳入。
$body = preg_replace('/^[ \t]*@piFixture\s*\([^)]*\)[ \t]*\r?\n/m', '', $body);

$body = trim($body) . "\n";

// 交出去之前先確認沒有殘留 —— 漏一行就是前端貼上後直接爆掉
foreach (['@piFragment', '@piFixture'] as $leftover) {
    if (str_contains($body, $leftover)) {
        fwrite(STDERR, "移除 prototype 專用語法後仍有殘留：{$leftover}。請檢查 fragment 的寫法。\n");
        exit(1);
    }
}

// ---------- 3. 輸出 ----------

if ($options['output'] === 'blade') {
    echo "{{-- ↓ 由 scripts/apply.php 產生，來源：prototypes/{$project}/fragments/{$name}.blade.php --}}\n";
    echo "{{-- 插入位置：" . ($manifest['target'] ?? '（manifest 未指定）') . " 的 slot `{$manifest['slot']}` --}}\n";
    echo "{{-- 資料改由 controller 傳入；需要的結構見 prototypes/{$project}/fixtures/ --}}\n\n";
    echo $body;
    exit(0);
}

if ($options['output'] !== 'patch') {
    fwrite(STDERR, "--output 只支援 blade 或 patch，收到 [{$options['output']}]\n");
    exit(1);
}

// --- patch 模式 ---

$targetPath = $options['target'];

if ($targetPath === null) {
    fwrite(STDERR, <<<TXT
    --output=patch 需要 --target 指到專案端的實體檔案。

      manifest 宣告的 target：{$manifest['target']}

      這是 "project:path" 形式的宣告，不是本機路徑 —— 本機 clone 在哪裡
      只有你知道，所以要明確給：

        php scripts/apply.php {$project} {$name} --output=patch \\
            --target=/你的路徑/{$manifest['target']}

    TXT);
    exit(1);
}

if (! is_file($targetPath)) {
    fwrite(STDERR, "target 檔案不存在：{$targetPath}\n");
    exit(1);
}

$targetSource = file_get_contents($targetPath);

if ($targetSource === false) {
    fwrite(STDERR, "讀不到 {$targetPath}\n");
    exit(1);
}

// slot marker 用 HTML 註解 —— blade 註解 render 後會消失，
// 而同一個 marker 必須同時能被 fetch-host.php 在 rendered HTML 裡找到。
$markerPattern = '/^([ \t]*)<!--\s*@pi-slot:\s*' . preg_quote($manifest['slot'], '/') . '\s*-->[ \t]*$/m';

if (! preg_match($markerPattern, $targetSource, $markerMatch)) {
    fwrite(STDERR, <<<TXT
    在 target 檔案裡找不到 slot marker：

        <!-- @pi-slot: {$manifest['slot']} -->

    專案端要先埋這一行（HTML 註解，不是 blade 註解）。

    TXT);
    exit(1);
}

$indent = $markerMatch[1];

// 依 marker 的縮排把片段整體縮排，貼進去才不會破壞既有排版
$indented = implode("\n", array_map(
    fn (string $line) => $line === '' ? '' : $indent . $line,
    explode("\n", rtrim($body))
));

$replacement = $markerMatch[0] . "\n\n" . $indented;
$patched = preg_replace($markerPattern, addcslashes($replacement, '\\$'), $targetSource, 1);

// 用 diff 產 unified patch。寫到暫存檔而不是 process substitution，
// 這樣在任何 sh 下都能跑。
$tmpOld = tempnam(sys_get_temp_dir(), 'pi-old-');
$tmpNew = tempnam(sys_get_temp_dir(), 'pi-new-');
file_put_contents($tmpOld, $targetSource);
file_put_contents($tmpNew, $patched);

/**
 * patch 的 a/ b/ 標籤要是「相對專案 repo 根」的路徑，`git apply` 才對得上。
 * 往上找最近的 .git 當根；找不到就退回檔名（此時前端得自己調 -p 參數）。
 */
$relative = (function (string $path): string {
    $dir = dirname($path);

    while ($dir !== '/' && $dir !== '.' && $dir !== '') {
        if (file_exists($dir . '/.git')) {
            return ltrim(substr($path, strlen($dir)), '/');
        }

        $parent = dirname($dir);

        if ($parent === $dir) {
            break;
        }

        $dir = $parent;
    }

    return basename($path);
})($targetPath);

$command = sprintf(
    'diff -u --label %s --label %s %s %s',
    escapeshellarg('a/' . $relative),
    escapeshellarg('b/' . $relative),
    escapeshellarg($tmpOld),
    escapeshellarg($tmpNew)
);

exec($command, $diffLines, $diffExit);

unlink($tmpOld);
unlink($tmpNew);

// diff 的 exit code：0＝無差異，1＝有差異（正常），>1＝出錯
if ($diffExit > 1) {
    fwrite(STDERR, "diff 執行失敗（exit {$diffExit}）\n");
    exit(1);
}

if ($diffExit === 0) {
    fwrite(STDERR, "target 檔案已包含相同內容，沒有產生 patch。\n");
    exit(0);
}

echo implode("\n", $diffLines) . "\n";
