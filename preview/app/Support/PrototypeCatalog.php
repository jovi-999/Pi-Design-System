<?php

namespace App\Support;

use Illuminate\Support\Collection;
use PiTw\PiDesignSystem\Prototype\Manifest;
use PiTw\PiDesignSystem\Prototype\StyleBudget;
use RuntimeException;

/**
 * 掃 repo 根的 prototypes/，組出 prototype 清單並解析 fragment manifest。
 *
 * prototypes/ 刻意不走 vendor/ ——它不出貨（.gitattributes export-ignore），
 * 是討論區而不是套件內容。
 */
class PrototypeCatalog
{
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
            'manifest' => Manifest::parse($source),
            // 用套件的 StyleBudget 而不是自己數：同一份數字也被
            // scripts/check-prototypes.php（CI 阻擋）使用，兩邊分開實作遲早
            // 一邊算 25 一邊算 32，然後沒人知道該信哪個。
            'styleBudget' => StyleBudget::measure($source),
        ];
    }

}
