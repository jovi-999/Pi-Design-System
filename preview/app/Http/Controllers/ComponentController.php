<?php

namespace App\Http\Controllers;

use App\Support\ComponentCatalog;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * 元件目錄與單一元件頁。
 *
 * 兩頁都由 meta 檔自動生成 —— 新增元件不需要碰這支 controller，
 * 也不需要改 preview 的任何頁面。
 */
class ComponentController extends Controller
{
    public function index(): View
    {
        return view('gallery.index', [
            'components' => ComponentCatalog::all(),
        ]);
    }

    public function show(string $slug): View
    {
        $component = ComponentCatalog::find($slug);

        if ($component === null) {
            throw new NotFoundHttpException("找不到元件 [{$slug}]");
        }

        return view('gallery.show', [
            'component' => $component,
            'components' => ComponentCatalog::all(),
            'rendered' => $this->renderExamples($component['examples']),
        ]);
    }

    /**
     * 把 meta 裡的 example code 字串 render 成 HTML。
     *
     * 用 Blade::render()（compile 任意字串）而不是實體 view 檔：
     * meta 的 examples 就是唯一來源，不需要為每個範例多存一個檔。
     *
     * 失敗時回傳錯誤訊息而不是讓整頁 500 —— 一個範例寫壞不該讓
     * 其他範例也看不到，而且錯誤要看得見才會被修。
     *
     * @param  array<int, array{label?: string, code: string}>  $examples
     * @return array<int, array{label: string, code: string, html: string, error: ?string}>
     */
    protected function renderExamples(array $examples): array
    {
        return collect($examples)->map(function (array $example, int $i) {
            $code = trim($example['code'] ?? '');

            try {
                $html = Blade::render($code);
                $error = null;
            } catch (Throwable $e) {
                $html = '';
                $error = $e->getMessage();
            }

            return [
                'label' => $example['label'] ?? '範例 ' . ($i + 1),
                'code' => $code,
                'html' => $html,
                'error' => $error,
            ];
        })->all();
    }
}
