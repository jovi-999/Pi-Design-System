<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * 掃 repo 根的 prototypes/，組出 prototype 清單並解析 fragment manifest。
 *
 * prototypes/ 刻意不走 vendor/ ——它不出貨（.gitattributes export-ignore），
 * 是討論區而不是套件內容。
 */
class PrototypeCatalog
{
    /** manifest 的三個欄位（spec 5.3） */
    public const MANIFEST_KEYS = ['target', 'slot', 'host'];

    public static function path(): string
    {
        // preview/ 的上一層就是 repo 根
        $path = realpath(base_path('..') . '/prototypes');

        if ($path === false) {
            throw new RuntimeException('找不到 prototypes/ 目錄（應在 repo 根）。');
        }

        return $path;
    }

    /**
     * 全部 prototype，以 "project/name" 為 key。
     *
     * @return Collection<string, array>
     */
    public static function all(): Collection
    {
        $items = collect();

        foreach (glob(static::path() . '/*', GLOB_ONLYDIR) as $projectDir) {
            $project = basename($projectDir);

            // _archive 之類的底線目錄不是專案
            if (str_starts_with($project, '_')) {
                continue;
            }

            foreach (['pages', 'fragments'] as $kind) {
                foreach (glob("{$projectDir}/{$kind}/*.blade.php") as $file) {
                    $name = basename($file, '.blade.php');
                    $items->put("{$project}/{$name}", static::load($project, $name, $kind, $file));
                }
            }
        }

        return $items->sortKeys();
    }

    public static function find(string $project, string $name): ?array
    {
        // 防目錄跳脫：只允許小寫英數、dash、dot（fragment 檔名有 dot）
        if (! preg_match('/^[a-z0-9\-]+$/', $project) || ! preg_match('/^[a-z0-9.\-]+$/', $name)) {
            return null;
        }
        if (str_contains($name, '..')) {
            return null;
        }

        foreach (['pages', 'fragments'] as $kind) {
            $file = static::path() . "/{$project}/{$kind}/{$name}.blade.php";

            if (is_file($file)) {
                return static::load($project, $name, $kind, $file);
            }
        }

        return null;
    }

    protected static function load(string $project, string $name, string $kind, string $file): array
    {
        $source = file_get_contents($file) ?: '';

        return [
            'project' => $project,
            'name' => $name,
            'kind' => $kind === 'pages' ? 'page' : 'fragment',
            'file' => $file,
            // 相對 repo 根的路徑，顯示用
            'relativePath' => 'prototypes/' . $project . '/' . $kind . '/' . $name . '.blade.php',
            'manifest' => static::parseManifest($source),
            'pageScopedScssLines' => static::countInlineStyleLines($source),
        ];
    }

    /**
     * 從 blade 原始碼靜態解析 `@piFragment([...])`。
     *
     * 為什麼用靜態解析而不是 runtime：manifest 是給 preview 與
     * scripts/apply.php 讀的中介資料，兩者都需要「不 render 就知道內容」。
     * apply.php 更是完全在 Laravel 之外執行。
     *
     * @return array<string, string> 空陣列＝這不是 fragment（或沒宣告 manifest）
     */
    public static function parseManifest(string $source): array
    {
        if (! preg_match('/@piFragment\s*\(\s*\[(.*?)\]\s*\)/s', $source, $matches)) {
            return [];
        }

        $manifest = [];

        foreach (static::MANIFEST_KEYS as $key) {
            // 只認單／雙引號的字面值 —— manifest 不該有運算式
            if (preg_match("/['\"]" . $key . "['\"]\s*=>\s*['\"](.*?)['\"]/", $matches[1], $m)) {
                $manifest[$key] = $m[1];
            }
        }

        return $manifest;
    }

    /**
     * 數 <style> 區塊的行數 —— CLAUDE.md 第 3 條的 30 行門檻。
     *
     * 這是健康指標而不是硬性阻擋：數字浮出來，超標的自然會被 review 注意到。
     */
    protected static function countInlineStyleLines(string $source): int
    {
        if (! preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $source, $matches)) {
            return 0;
        }

        $lines = 0;

        foreach ($matches[1] as $block) {
            foreach (explode("\n", trim($block)) as $line) {
                if (trim($line) !== '') {
                    $lines++;
                }
            }
        }

        return $lines;
    }
}
