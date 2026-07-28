<?php

return [
    'name' => 'Loading',
    'scss' => 'resources/scss/components/_loading.scss',
    'description' => '載入中轉圈。SCSS 只負責旋轉動畫，尺寸與顏色由使用端指定。',

    'props' => [
        'icon' => '旋轉的 icon class（預設 icon-_loading）',
        'size' => "CSS font-size 字串，如 '24px'",
        'color' => "CSS color 字串，如 'var(--cl-green-500)'",
    ],

    'notes' => [
        '設計系統沒有 gl_loading-{size} 這類 modifier，所以 size / color 直接吐 inline style —— 與既有 preview markup 的做法一致，不自創 class。',
        '常見用法是塞進按鈕內表示送出中。',
    ],

    'examples' => [
        [
            'label' => '尺寸與顏色',
            'code' => <<<'BLADE'
            <x-pi::loading size="16px" />
            <x-pi::loading size="24px" color="var(--cl-green-500)" />
            <x-pi::loading size="32px" color="var(--cl-blue-500)" />
            BLADE,
        ],
        [
            'label' => '按鈕內',
            'code' => <<<'BLADE'
            <x-pi::button tone="success" disabled>
                <x-pi::loading size="18px" /> 送出中…
            </x-pi::button>
            BLADE,
        ],
    ],
];
