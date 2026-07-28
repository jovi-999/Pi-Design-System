<?php

declare(strict_types=1);

namespace PiTw\PiDesignSystem\Prototype;

use RuntimeException;

/**
 * Prototype 的 fixture 載入器。
 *
 * 為什麼需要這個類別，而不是照 spec 直接寫
 * `@php($x = include __DIR__.'/../fixtures/x.php')`：
 *
 *   Blade 會把 view 編譯成 storage/framework/views/ 底下的快取檔再執行，
 *   所以編譯後的 `__DIR__` 是**快取目錄**，不是 prototype 原始檔的位置。
 *   spec 那個寫法在任何 Laravel 版本都不會work —— 這是 spec 的缺陷。
 *
 * 改用 `@piFixture($statuses, 'member-status')`：
 *   - 路徑由 render 端（preview controller）設定，blade 不需要知道自己在哪
 *   - 仍然是「交接時刪掉的一行」，而且比 raw include 更明顯是 prototype 專用
 */
class FixtureLoader
{
    /** 目前 prototype 的 fixtures/ 目錄絕對路徑。 */
    private static ?string $baseDir = null;

    /**
     * 由 render 端在 render 前設定（preview 的 PrototypeController 會呼叫）。
     */
    public static function useDirectory(?string $dir): void
    {
        self::$baseDir = $dir;
    }

    /**
     * 載入一份 fixture。
     *
     * @param  string  $name  檔名（不含 .php）
     * @return mixed  fixture 檔 return 的內容
     */
    public static function load(string $name): mixed
    {
        if (self::$baseDir === null) {
            throw new RuntimeException(
                '@piFixture 需要先設定 fixtures 目錄。'
                . '這通常表示 prototype 被直接 render，而不是透過 preview 的 PrototypeController。'
            );
        }

        // 防目錄跳脫：fixture 名稱只允許小寫英數、dash、底線
        if (! preg_match('/^[a-z0-9_\-]+$/', $name)) {
            throw new RuntimeException("fixture 名稱不合法：[{$name}]");
        }

        $path = self::$baseDir . '/' . $name . '.php';

        if (! is_file($path)) {
            throw new RuntimeException(
                "找不到 fixture [{$name}]，預期路徑：{$path}"
            );
        }

        return require $path;
    }
}
