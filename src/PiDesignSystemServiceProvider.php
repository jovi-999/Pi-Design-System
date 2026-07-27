<?php

declare(strict_types=1);

namespace Company\PiDesignSystem;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Pi Design System 的 Laravel 接點。
 *
 * 這個套件出貨兩層東西：
 *
 *   resources/scss/   SCSS 原始碼（框架中立）。**沒有 build step** ——
 *                     專案端用自己的 Vite / sass 去 @use，才吃得到專案
 *                     自己的變數覆寫。若這裡先編成 CSS 就失去這個能力。
 *   resources/views/  Blade 元件。這一層才綁 Laravel。
 *
 * 本 ServiceProvider 只負責 Blade 那一層；SCSS 不經過 PHP。
 */
class PiDesignSystemServiceProvider extends ServiceProvider
{
    /**
     * Blade 命名空間。決定元件的呼叫寫法：`<x-pi::button />`。
     *
     * 沿用 Pi DS 既有的 `pi` / `gl_` 命名，未採用 spec 建議的 `dg`。
     */
    private const NAMESPACE = 'pi';

    public function boot(): void
    {
        $views = __DIR__ . '/../resources/views';

        // 讓 `view('pi::…')` 解得到（layouts 等非元件 view 用）
        $this->loadViewsFrom($views, self::NAMESPACE);

        // Anonymous component path：一個 blade 檔就是一個元件，不需要寫 PHP
        // class、也不需要註冊清單。`<x-pi::button>` 自動對應到
        // resources/views/components/button.blade.php。
        //
        // 目錄可能還不存在（元件轉換是 Phase 2），先擋掉避免註冊到空路徑。
        $components = $views . '/components';
        if (is_dir($components)) {
            Blade::anonymousComponentPath($components, self::NAMESPACE);
        }

        $this->registerFragmentDirective();
    }

    /**
     * `@piFragment([...])` —— prototype fragment 的 manifest 宣告。
     *
     * 宣告 fragment 要插進專案的哪個位置：
     *
     *   @piFragment([
     *       'target' => 'project-a:members/index.blade.php',  // 插入的專案檔案
     *       'slot'   => 'member-list.filters',                // 專案 blade 內的 marker 名稱
     *       'host'   => 'project-a/_hosts/members-index.html' // preview 時的宿主快照
     *   ])
     *
     * 目前刻意「render 成空字串」：manifest 是給 preview controller 與
     * scripts/apply.php 靜態讀取的中介資料（用正則掃 blade 原始碼），
     * 不參與 render。等 Phase 3 的 fragment 系統做好再決定是否需要在
     * runtime 消費它。
     *
     * 沒有這個 directive，帶 manifest 的 prototype blade 會直接編譯失敗
     * （Blade 會把未知的 @piFragment 當純文字輸出，manifest 內容會漏到畫面上）。
     */
    private function registerFragmentDirective(): void
    {
        Blade::directive('piFragment', static fn (string $expression): string => '');
    }
}
