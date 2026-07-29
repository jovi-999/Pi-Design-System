<?php

/**
 * 表單下方 CTA 區塊的文案。
 *
 * ⚠️ 文案是 AI 隨意給的預設值（PM 指示「先隨意給」）—— 定稿前請直接改這個檔，
 * 不要改 blade。放進 fixture 而不寫死在 blade，是因為文案的擁有者是 PM／內容端，
 * 換文案不該動到版面程式碼。
 *
 * 資料契約：title / body / action.label / action.href。
 * href 目前是 '#'，實際登入頁路由待後端補。
 */

return [
    'tone' => 'info',
    'icon' => 'icon-info',
    'title' => '已經有帳號了？',
    'body' => '用原本的 Email 登入就能看到你的職缺收藏與應徵紀錄。',
    'action' => [
        'label' => '前往登入',
        'href' => '#',
    ],
];
