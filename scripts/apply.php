<?php

/**
 * 把定案的 prototype 轉成可套進專案的內容。fragment 與 page 都支援。
 *
 * 用法：
 *   php scripts/apply.php <project> <name> [--output=blade|patch] [--target=<專案檔絕對路徑>]
 *
 * 例：
 *   php scripts/apply.php project-a member-list.filters
 *   php scripts/apply.php project-a member-list.filters --output=patch --target=/srv/project-a/resources/views/members/index.blade.php
 *   php scripts/apply.php project-a salary-report
 *
 * 做三件事：
 *   1. 判定是 fragment 還是 page；fragment 讀 @piFragment manifest 取得 target / slot
 *   2. 移除 prototype 專用的內容（@piFragment、@piFixture，以及只在 prototype
 *      情境下才有意義的說明註解）
 *   3. 輸出 blade，或（fragment + --target 時）產生 unified diff patch
 *
 * `--output=patch` 只適用 fragment：page 是整頁搬進專案的新檔案，沒有「插進既有
 * 檔案的某個位置」這件事，產 diff 沒有意義。
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
    用法：php scripts/apply.php <project> <name> [--output=blade|patch] [--target=<專案檔路徑>]

      <name>           fragment 或 page 的檔名（不含 .blade.php）
      --output=blade   （預設）印出可貼上的 blade
      --output=patch   產生 unified diff。**只適用 fragment**，且需要 --target
      --target=…       專案端目標檔的絕對路徑（manifest 的 target 是 project:path
                       宣告，不是本機路徑，所以要明確給）

    TXT);
    exit(1);
}

// fragment 與 page 都支援。fragment 先找 —— 兩者同名時 fragment 才有 manifest，
// 是更明確的意圖。
$kind = null;
$sourceFile = null;

foreach (['fragments' => 'fragment', 'pages' => 'page'] as $dir => $candidateKind) {
    $candidate = "{$root}/prototypes/{$project}/{$dir}/{$name}.blade.php";

    if (is_file($candidate)) {
        $kind = $candidateKind;
        $sourceFile = $candidate;
        break;
    }
}

if ($sourceFile === null) {
    fwrite(STDERR, <<<TXT
    找不到 prototype [{$project}/{$name}]。找過：
      prototypes/{$project}/fragments/{$name}.blade.php
      prototypes/{$project}/pages/{$name}.blade.php

    TXT);
    exit(1);
}

$source = file_get_contents($sourceFile);

if ($source === false) {
    fwrite(STDERR, "讀不到 {$sourceFile}\n");
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

// slot 只有 fragment 需要 —— page 是整頁搬進專案，沒有插入點的概念。
if ($kind === 'fragment' && empty($manifest['slot'])) {
    fwrite(STDERR, "fragment 沒有宣告 @piFragment 的 slot，無法決定插入位置。\n");
    exit(1);
}

// ---------- 2. 移除 prototype 專用的內容 ----------

/**
 * `preg_replace` 失敗會回傳 null（例如撞到 backtrack limit）。
 * 這裡的每一步都是「把不該交出去的東西拿掉」，靜默變成 null 會讓後面的
 * 殘留檢查對 null 做比對而通過 —— 正是本段要防的事，所以失敗就中止。
 */
$mustReplace = static function (string $pattern, string $replacement, string $subject, string $what): string {
    $result = preg_replace($pattern, $replacement, $subject);

    if ($result === null) {
        fwrite(STDERR, "移除「{$what}」時 regex 執行失敗（" . preg_last_error_msg() . "）。未產出任何內容。\n");
        exit(1);
    }

    return $result;
};

$body = $source;

// @piFragment([...])：可能跨多行
$body = $mustReplace('/@piFragment\s*\(\s*\[.*?\]\s*\)\s*/s', '', $body, '@piFragment manifest');

// @piFixture(...)：整行刪掉。專案端資料由 controller 傳入。
$body = $mustReplace('/^[ \t]*@piFixture\s*\([^)]*\)[ \t]*\r?\n/m', '', $body, '@piFixture');

// 只在 prototype 情境下才有意義的說明註解也要拿掉。
//
// 原因：prototype 檔頭常寫「交接時刪掉下面兩行 @piFixture」。那兩行已經在上面
// 被移除了，把這段指示原樣交給前端，會讓人去找一組不存在的行。
// 判準：blade 註解裡提到 @piFixture 或 @piFragment 的，就是講 prototype 機制的
// 註解；講設計決策的註解（例如組合件、元件缺口）不含這兩個字，會被保留。
$body = $mustReplace(
    '/\{\{--(?:(?!--\}\}).)*?@pi(?:Fixture|Fragment)(?:(?!--\}\}).)*?--\}\}\s*/s',
    '',
    $body,
    'prototype 機制說明註解'
);

$body = trim($body) . "\n";

// 交出去之前先確認沒有殘留 —— 漏一行就是前端貼上後直接爆掉。
// 只認帶括號的呼叫，不認散文裡的提及。
foreach (['@piFragment', '@piFixture'] as $leftover) {
    if (preg_match('/' . preg_quote($leftover, '/') . '\s*\(/', $body)) {
        fwrite(STDERR, "移除 prototype 專用語法後仍有殘留：{$leftover}。請檢查 prototype 的寫法。\n");
        exit(1);
    }
}

// ---------- 3. 輸出 ----------

$relativeSource = str_replace($root . '/', '', $sourceFile);

if ($options['output'] === 'blade') {
    echo "{{-- ↓ 由 scripts/apply.php 產生，來源：{$relativeSource} --}}\n";

    if ($kind === 'fragment') {
        echo "{{-- 插入位置：" . ($manifest['target'] ?? '（manifest 未指定）')
            . " 的 slot `{$manifest['slot']}` --}}\n";
    } else {
        echo "{{-- 這是整頁 prototype。貼進專案後把下面的 @extends 換成專案自己的 layout； --}}\n";
        echo "{{-- @section('content') 內的 body 一個字都不用改。 --}}\n";
    }

    echo "{{-- 資料改由 controller 傳入；需要的結構見 prototypes/{$project}/fixtures/ --}}\n\n";
    echo $body;
    exit(0);
}

if ($options['output'] !== 'patch') {
    fwrite(STDERR, "--output 只支援 blade 或 patch，收到 [{$options['output']}]\n");
    exit(1);
}

// --- patch 模式 ---
//
// 只有 fragment 有 patch 模式：page 是整頁搬進專案的新檔案，沒有「插進既有檔案
// 的某個位置」這件事，產 diff 沒有意義。
if ($kind === 'page') {
    fwrite(STDERR, <<<TXT
    --output=patch 只支援 fragment。

      [{$name}] 是 page prototype —— 整頁搬進專案是「新增一個檔案」，
      不是插進既有檔案的某個位置，所以沒有 patch 可產。

      改用：php scripts/apply.php {$project} {$name}

    TXT);
    exit(1);
}

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
