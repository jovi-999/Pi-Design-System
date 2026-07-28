<?php

/**
 * Pi DS — Button 的元件 metadata。
 *
 * 兩個消費者：
 *   1. preview 的元件目錄頁：掃資料夾自動 render examples，新增元件不用改 preview
 *   2. scripts/sync-component-list.php：生成 CLAUDE.md 的可用元件清單
 *
 * 所以 examples 的 code 必須是「可以直接 render 的合法 blade」。
 */

return [
    'name' => 'Button',
    'scss' => 'resources/scss/components/_button.scss',
    'description' => '操作按鈕。5 種 variant × 8 種 tone × 5 種尺寸，另有純 icon 型。',

    'props' => [
        'variant' => ['solid', 'outline', 'ghost', 'secondary', 'gray'],
        'tone' => ['basic', 'dark', 'info', 'success', 'warning', 'danger', 'orange', 'purple'],
        'size' => ['xl', 'lg', 'md', 'sm', 'xs'],
        'icon' => 'icon class 字串；給了就變純 icon 按鈕（自動加 .gl_btn-icon），需自行給 aria-label',
        'iconLeft' => 'icon class 字串，放在文字左側',
        'iconRight' => 'icon class 字串，放在文字右側',
        'as' => ['button', 'a'],
    ],

    'notes' => [
        'dark 只有 solid 有（.gl_btn-dark）。outline / ghost / secondary / gray 沒有 dark，傳了會丟例外。',
        '停用狀態直接給 disabled 屬性，或加 class="gl_disabled"（SCSS 兩者都吃）。',
    ],

    'examples' => [
        [
            'label' => '尺寸',
            'code' => <<<'BLADE'
            <x-pi::button size="xl" tone="success">XL 按鈕</x-pi::button>
            <x-pi::button size="lg" tone="success">LG 按鈕</x-pi::button>
            <x-pi::button size="md" tone="success">MD 按鈕</x-pi::button>
            <x-pi::button size="sm" tone="success">SM 按鈕</x-pi::button>
            <x-pi::button size="xs" tone="success">XS 按鈕</x-pi::button>
            BLADE,
        ],
        [
            'label' => 'Solid 色系',
            'code' => <<<'BLADE'
            <x-pi::button tone="basic">Basic</x-pi::button>
            <x-pi::button tone="dark">Dark</x-pi::button>
            <x-pi::button tone="info">Blue</x-pi::button>
            <x-pi::button tone="success">Green</x-pi::button>
            <x-pi::button tone="warning">Yellow</x-pi::button>
            <x-pi::button tone="danger">Red</x-pi::button>
            <x-pi::button tone="orange">Orange</x-pi::button>
            <x-pi::button tone="purple">Purple</x-pi::button>
            BLADE,
        ],
        [
            'label' => 'Variant',
            'code' => <<<'BLADE'
            <x-pi::button variant="solid" tone="success">Solid</x-pi::button>
            <x-pi::button variant="outline" tone="success">Outline</x-pi::button>
            <x-pi::button variant="ghost" tone="success">Ghost</x-pi::button>
            <x-pi::button variant="secondary" tone="success">Secondary</x-pi::button>
            <x-pi::button variant="gray" tone="success">Gray</x-pi::button>
            BLADE,
        ],
        [
            'label' => 'Icon 組合',
            'code' => <<<'BLADE'
            <x-pi::button tone="success">純文字</x-pi::button>
            <x-pi::button tone="success" icon-left="icon-search">左 icon</x-pi::button>
            <x-pi::button tone="success" icon-right="icon-download">右 icon</x-pi::button>
            <x-pi::button tone="success" icon-left="icon-search" icon-right="icon-download">左右 icon</x-pi::button>
            <x-pi::button tone="success" icon="icon-search" aria-label="搜尋" />
            BLADE,
        ],
        [
            'label' => '停用',
            'code' => <<<'BLADE'
            <x-pi::button tone="success" disabled>停用</x-pi::button>
            <x-pi::button variant="outline" tone="success" disabled>停用</x-pi::button>
            BLADE,
        ],
    ],
];
