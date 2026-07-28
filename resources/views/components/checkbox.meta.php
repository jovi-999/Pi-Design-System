<?php

return [
    'name' => 'Checkbox',
    'scss' => 'resources/scss/components/_checkbox.scss',
    'description' => '核取方塊。勾選色隨 tone 變化，勾勾是內嵌 SVG（不吃 icon 字型）。',

    'props' => [
        'tone' => ['basic', 'info', 'success', 'danger'],
        'name' => '欄位名稱',
        'value' => '送出值',
        'checked' => 'bool',
        'disabled' => 'bool',
        'labelSize' => ['fz-body-sm', 'fz-title-sm'],
    ],

    'slots' => ['default' => '標籤文字'],

    'notes' => [
        'checkbox 只有 basic / info / success / danger —— 沒有 warning / purple / orange，與 button 的 tone 清單不同。',
        'labelSize 只能是 fz-body-sm 或 fz-title-sm：SCSS 只對這兩個 class 補 margin-left，換別的字級文字會貼在方框上。',
    ],

    'examples' => [
        [
            'label' => '各色系',
            'code' => <<<'BLADE'
            <x-pi::checkbox tone="success" checked>訂閱每週職缺摘要</x-pi::checkbox>
            <x-pi::checkbox tone="success">接受匿名面試回饋</x-pi::checkbox>
            <x-pi::checkbox tone="info" checked>使用 Info 色</x-pi::checkbox>
            <x-pi::checkbox tone="danger" checked>同意移除帳號與所有資料</x-pi::checkbox>
            <x-pi::checkbox tone="basic" checked>Basic</x-pi::checkbox>
            BLADE,
        ],
    ],
];
