<?php

return [
    'name' => 'Toggle',
    'scss' => 'resources/scss/components/_toggle.scss',
    'description' => '開關。純 CSS 實作（input + knobs + layer 三層），無需 JS。',

    'props' => [
        'size' => ['lg', 'md', 'sm', 'xs'],
        'tone' => ['basic', 'info', 'success', 'warning', 'danger'],
        'name' => '欄位名稱',
        'value' => '送出值',
        'checked' => 'bool',
        'disabled' => 'bool',
    ],

    'notes' => [
        'toggle 沒有 xl 尺寸，也沒有 purple / orange。',
        'input / knobs / layer 的順序不可調換 —— SCSS 用相鄰與後續兄弟選擇器（+ .knobs、~ .layer）驅動狀態。',
        '沒有標籤 slot：.gl_toggle 本身就是 <label>，文字要另外放在旁邊。',
    ],

    'examples' => [
        [
            'label' => '尺寸',
            'code' => <<<'BLADE'
            <x-pi::toggle size="lg" tone="success" checked />
            <x-pi::toggle size="md" tone="success" checked />
            <x-pi::toggle size="sm" tone="success" checked />
            <x-pi::toggle size="xs" tone="success" checked />
            BLADE,
        ],
        [
            'label' => '色系與關閉狀態',
            'code' => <<<'BLADE'
            <x-pi::toggle tone="basic" checked />
            <x-pi::toggle tone="info" checked />
            <x-pi::toggle tone="warning" checked />
            <x-pi::toggle tone="danger" checked />
            <x-pi::toggle tone="success" />
            BLADE,
        ],
    ],
];
