<?php

declare(strict_types=1);

namespace PiTw\PiDesignSystem\Prototype;

/**
 * Prototype 的 class 命名空間檢查（CLAUDE.md 的命名空間規則）。
 *
 * Prototype 裡只准出現三種 class：
 *
 *   gl_ / fz- / text- / icon-   設計系統出貨的公共樣式
 *   pt-                         prototype 自己的 page-scoped 排版
 *   （Blade 插值）               {{ $x }} 之類，執行期才知道，跳過
 *
 * 為什麼 page-scoped 用固定的 `pt-` 而不是各專案的縮寫：
 *
 *   原本的規則是「用專案縮寫」（interview → iv-、jobar → jb-）。問題是縮寫
 *   沒有登記在任何地方，誰產 prototype 誰現取 —— interview 專案自己的私有前綴
 *   明明是 iw_，prototype 卻取了 iv-，同一個專案兩個縮寫。
 *
 *   而且那條規則防不了它想防的事：同專案的所有 prototype 本來就共用一個縮寫，
 *   兩份 prototype 各寫一個 .iv-card 照樣會撞。改成固定 `pt-` 後同專案內的
 *   碰撞風險完全沒變，卻換到「允許集合是固定的四個前綴」——
 *   這才讓 CI 有辦法檢查。
 *
 * 為什麼 pv- 要單獨擋下並給不同訊息：
 *
 *   pv- 只存在於 preview app，不在套件裡。用了它 prototype 在 preview 看起來
 *   正常，貼進專案卻完全沒樣式 —— 這種「在原處對、到目的地錯」的 bug 最難查，
 *   值得一條專屬的錯誤訊息直接點破。
 *
 * 這個類別刻意不依賴 Laravel，理由同 StyleBudget：CI 不該為了掃 class 名而
 * boot 一個 framework。
 */
class ClassPrefix
{
    /** page-scoped 排版專用的前綴。 */
    public const PAGE_SCOPED = 'pt-';

    /** 設計系統出貨、prototype 可以直接用的前綴。 */
    public const DESIGN_SYSTEM = ['gl_', 'fz-', 'text-', 'icon-'];

    /** preview app 專用，不在套件裡 —— 出現在 prototype 一定是錯的。 */
    public const PREVIEW_ONLY = 'pv-';

    /**
     * 找出一份 prototype 原始碼裡所有不合規的 class 名。
     *
     * @return array<string, string> class 名 => 為什麼不行（同一個 class 只回報一次）
     */
    public static function violations(string $source): array
    {
        $problems = [];

        foreach (static::classNames($source) as $name) {
            $reason = static::reject($name);

            if ($reason !== null) {
                $problems[$name] = $reason;
            }
        }

        ksort($problems);

        return $problems;
    }

    /**
     * 這個 class 名不合規的原因；合規則回傳 null。
     */
    public static function reject(string $name): ?string
    {
        if (str_starts_with($name, self::PAGE_SCOPED)) {
            return null;
        }

        foreach (self::DESIGN_SYSTEM as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return null;
            }
        }

        if (str_starts_with($name, self::PREVIEW_ONLY)) {
            return sprintf(
                '`%s` 是 preview app 專用的前綴，套件裡沒有這些 class —— '
                . 'prototype 在 preview 會看起來正常，貼進專案卻完全沒樣式。改用 `%s`。',
                self::PREVIEW_ONLY,
                self::PAGE_SCOPED
            );
        }

        // 專案私有前綴（iw_ / sa_ / ta_ …）：底線結尾的兩三個字母
        if (preg_match('/^[a-z]{2,3}_/', $name) === 1) {
            return sprintf(
                '看起來是某個專案的私有前綴。prototype 的 page-scoped 排版一律用 `%s`，'
                . '不要借用專案的命名空間。',
                self::PAGE_SCOPED
            );
        }

        return sprintf(
            'prototype 只能用 `%s`（page-scoped 排版）或設計系統的 `%s`。',
            self::PAGE_SCOPED,
            implode('` / `', self::DESIGN_SYSTEM)
        );
    }

    /**
     * 掃出原始碼裡所有 class 名 —— HTML 的 class 屬性與 `<style>` 區塊的選擇器都算。
     *
     * 只掃 class 屬性不夠：樣式可能宣告了卻沒用（改版殘留），那種一樣要被抓到。
     * 反過來只掃選擇器也不夠：HTML 可能用了未定義的 class。
     *
     * @return array<int, string> 去重後的 class 名
     */
    public static function classNames(string $source): array
    {
        $names = [];

        // 1. HTML 的 class="…"
        preg_match_all('/\bclass\s*=\s*"([^"]*)"/i', $source, $matches);

        foreach ($matches[1] as $attribute) {
            // Blade 插值執行期才知道值，整段剝掉再切。
            // 必須在切開之前剝 —— `class="a {{ $x }}"` 用空白切會得到
            // `{{` / `$x` / `}}` 三個 token，中間那個不含大括號，
            // 逐 token 判斷會把 `$x` 當成 class 名回報。
            $attribute = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}|@\w+\(.*?\)/s', ' ', $attribute) ?? '';

            foreach (preg_split('/\s+/', $attribute) ?: [] as $token) {
                if ($token === '') {
                    continue;
                }

                $names[] = $token;
            }
        }

        // 2. <style> 區塊裡的 .class 選擇器
        foreach (StyleBudget::styleBlocks($source) as $block) {
            if (preg_match_all('/\.(-?[_a-zA-Z][\w-]*)/', $block, $matches) !== false) {
                foreach ($matches[1] as $name) {
                    $names[] = $name;
                }
            }
        }

        return array_values(array_unique($names));
    }
}
