<?php

return [
    'name' => 'Radio',
    'scss' => 'resources/scss/components/_radio.scss',
    'description' => '單選鈕。選中色隨 tone 變化，圓點是 ::after 疊在 ::before 上。',

    'props' => [
        'tone' => ['basic', 'info', 'success', 'danger'],
        'name' => '同一組必須共用 name',
        'value' => '送出值',
        'checked' => 'bool',
        'disabled' => 'bool',
    ],

    'slots' => ['default' => '標籤文字'],

    'notes' => [
        'radio 只有 basic / info / success / danger —— 沒有 warning / purple。',
        '標籤字級固定 fz-body-sm：_radio.scss 只對這個 class 補了 margin-left（不像 checkbox 有兩種）。',
    ],

    'examples' => [
        [
            'label' => '一組單選',
            'code' => <<<'BLADE'
            <x-pi::radio tone="success" name="job-type" value="fulltime" checked>正職</x-pi::radio>
            <x-pi::radio tone="success" name="job-type" value="intern">實習 / 工讀</x-pi::radio>
            <x-pi::radio tone="success" name="job-type" value="contract">派遣 · 約聘</x-pi::radio>
            BLADE,
        ],
        [
            'label' => '各色系',
            'code' => <<<'BLADE'
            <x-pi::radio tone="basic" name="t" checked>Basic</x-pi::radio>
            <x-pi::radio tone="info" name="t2" checked>Info</x-pi::radio>
            <x-pi::radio tone="success" name="t3" checked>Success</x-pi::radio>
            <x-pi::radio tone="danger" name="t4" checked>Danger</x-pi::radio>
            BLADE,
        ],
    ],
];
