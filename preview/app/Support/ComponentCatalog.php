<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * 掃套件的 resources/views/components/*.meta.php，組出元件清單。
 *
 * 只有一個資料來源：meta 檔本身。新增元件時只要放
 * <name>.blade.php + <name>.meta.php，preview 目錄頁與 CLAUDE.md
 * 的元件清單都會自動跟上，不需要改任何一份清單。
 */
class ComponentCatalog
{
    /**
     * 套件內元件目錄的絕對路徑。
     *
     * 走 vendor/ 而不是 ../resources：讓 preview 讀到的跟專案端
     * composer require 之後讀到的是同一條路徑，路徑寫錯會當場發現。
     */
    public static function path(): string
    {
        $path = realpath(base_path('vendor/pi-tw/pi-design-system/resources/views/components'));

        if ($path === false) {
            throw new RuntimeException(
                '找不到套件的元件目錄。先在 preview/ 執行 `docker compose run --rm app composer install`。'
            );
        }

        return $path;
    }

    /**
     * 全部元件，以 slug 為 key，依 slug 排序。
     *
     * @return Collection<string, array>
     */
    public static function all(): Collection
    {
        return collect(glob(static::path() . '/*.meta.php'))
            ->mapWithKeys(function (string $file) {
                $slug = basename($file, '.meta.php');

                return [$slug => static::load($slug, $file)];
            })
            ->sortKeys();
    }

    /**
     * 單一元件。找不到回 null，由 controller 決定要不要 404。
     */
    public static function find(string $slug): ?array
    {
        // 防目錄跳脫：slug 只允許小寫英數與 dash（對應檔名慣例）
        if (! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            return null;
        }

        $file = static::path() . "/{$slug}.meta.php";

        return is_file($file) ? static::load($slug, $file) : null;
    }

    /**
     * 讀一份 meta 並補上衍生欄位。
     */
    protected static function load(string $slug, string $file): array
    {
        $meta = require $file;

        return array_merge([
            'name' => $slug,
            'description' => null,
            'props' => [],
            'slots' => [],
            'notes' => [],
            'examples' => [],
        ], $meta, [
            'slug' => $slug,
            'tag' => "x-pi::{$slug}",
            // blade 檔不存在就是漏了，直接顯示出來而不是安靜跳過
            'hasBlade' => is_file(static::path() . "/{$slug}.blade.php"),
        ]);
    }
}
