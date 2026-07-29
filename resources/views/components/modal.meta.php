<?php

return [
    'name' => 'Modal',
    'scss' => 'resources/scss/components/_modal.scss',
    'description' => '對話框面板。352px 寬、24px 圓角、白底加 xl 陰影。',

    'props' => [
        'icon' => '頂部 icon class',
        'iconClass' => 'icon 顏色，用 base/_utilities.scss 的 .text-* class（如 text-green-500）',
        'title' => '標題（fz-title-lg fz-tit）',
        'footerLayout' => ['flex-column', 'flex-row', 'flex-row flex-end'],
    ],

    'slots' => [
        'default' => '主內容（選用，會包進 .gl_modal-content）',
        'description' => '標題下方的說明文字',
        'footer' => '底部按鈕區',
    ],

    'notes' => [
        '⚠️ 這支只有「面板外觀」。遮罩、置中、開關、focus trap 都不在設計系統內 —— 需要時用原生 <dialog> 包住，或走元件缺口流程提報。',
        '已廢除的 preview-static/modal.html 用的 .gl_gs-modal 是那一頁自帶的 inline CSS（該檔註解寫明「此 namespace 無 src CSS，preview 專用」），不是套件的一部分，本元件不使用它。',
        'footer 的 flex-column 會讓按鈕 width:100%；flex-row 會平分寬度；flex-row flex-end 靠右且不撐開。',
    ],

    'examples' => [
        [
            'label' => '確認對話框（按鈕直排）',
            'code' => <<<'BLADE'
            <x-pi::modal
                icon="icon-shield-check"
                icon-class="text-green-500"
                title="匿名發布這則心得"
                footer-layout="flex-column"
            >
                <x-slot:description>公司不會看到你的姓名或 Email，只有面試流程與評價會被刊登。</x-slot:description>
                <x-slot:footer>
                    <x-pi::button size="sm" tone="success">確定送出</x-pi::button>
                    <x-pi::button size="sm" variant="gray" tone="basic">返回編輯</x-pi::button>
                </x-slot:footer>
            </x-pi::modal>
            BLADE,
        ],
        [
            'label' => '危險操作（按鈕橫排）',
            'code' => <<<'BLADE'
            <x-pi::modal
                icon="icon-alert-triangle"
                icon-class="text-red-500"
                title="刪除這則薪資回報？"
                footer-layout="flex-row"
            >
                <x-slot:description>刪除後無法還原，其他使用者看到的統計也會更新。</x-slot:description>
                <x-slot:footer>
                    <x-pi::button size="sm" variant="gray" tone="basic">取消</x-pi::button>
                    <x-pi::button size="sm" tone="danger">確定刪除</x-pi::button>
                </x-slot:footer>
            </x-pi::modal>
            BLADE,
        ],
    ],
];
