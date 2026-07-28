<?php

/**
 * Pi DS — Form control 的元件 metadata。
 */

return [
    'name' => 'Form control',
    'scss' => 'resources/scss/components/_form.scss',
    'description' => '表單欄位。含 input / select / textarea、前置 icon、說明文字與驗證回饋。',

    'props' => [
        'type' => '原生 input type，或 select / textarea',
        'name' => '欄位名稱',
        'value' => '預設值（select 時用來決定 selected）',
        'placeholder' => 'select 時會變成第一個空值 option',
        'options' => 'type=select 時的選項：[[value,label], …] 或 [值 => 標籤]',
        'icon' => '前置 icon class，會自動加上 .gl_icon-input-wrap',
        'prompt' => '欄位下方的說明文字',
        'promptTone' => [null, 'info', 'warning'],
        'promptIcon' => '說明文字的 icon class（預設 icon-info）',
        'state' => [null, 'valid', 'invalid'],
        'feedback' => '驗證訊息（要有 state 才有顏色）',
        'feedbackIcon' => '不給就依 state 自動選 icon-checked / icon-alert-triangle',
        'disabled' => 'bool',
    ],

    'notes' => [
        '.gl_form-control 的樣式定義在 .gl_form-group 底下，所以這支元件一定輸出完整群組結構，不提供單獨的 control。',
        'is-valid / is-invalid 掛在 group 上（SCSS 是 `.is-valid .form-feedback`），不是掛在 feedback 上。',
        '.is-valid / .is-invalid 與 Bootstrap 撞名。@layer pi 解得掉 specificity，語意衝突待 Phase 3 再議。',
    ],

    'examples' => [
        [
            'label' => 'Input',
            'code' => <<<'BLADE'
            <x-pi::form-control placeholder="職稱 · 公司 · 關鍵字" />
            <x-pi::form-control icon="icon-search" placeholder="搜尋公司或職位" />
            <x-pi::form-control value="已填入的內容" />
            <x-pi::form-control value="停用狀態" disabled />
            BLADE,
        ],
        [
            'label' => 'Select / Textarea',
            'code' => <<<'BLADE'
            <x-pi::form-control
                type="select"
                placeholder="請選擇產業"
                :options="['半導體', '金融服務', '電子商務']"
            />
            <x-pi::form-control type="textarea" placeholder="寫下面試心得 · 其他求職者會感謝你" />
            BLADE,
        ],
        [
            'label' => '說明文字',
            'code' => <<<'BLADE'
            <x-pi::form-control placeholder="最低期望月薪" prompt="以新台幣計算" />
            <x-pi::form-control placeholder="最低期望月薪" prompt="同職位中位數為 NT$ 52,800" prompt-tone="info" />
            <x-pi::form-control placeholder="最低期望月薪" prompt="請確認金額是否合理" prompt-tone="warning" prompt-icon="icon-alert-triangle" />
            BLADE,
        ],
        [
            'label' => '驗證狀態',
            'code' => <<<'BLADE'
            <x-pi::form-control value="johndoe@example.com" state="valid" feedback="Email 格式正確" />
            <x-pi::form-control value="johndoe" state="invalid" feedback="缺少 @ 與網域" />
            BLADE,
        ],
    ],
];
