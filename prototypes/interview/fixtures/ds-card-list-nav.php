<?php

/**
 * Fixture：ds-card-list 頁 sidebar 的導覽連結。
 *
 * current 為 true 的那一項是「當前頁」。
 * 這個狀態目前沒有元件可以表現 —— nav-link 是已同意待做的新件
 * （提議見 .scratch/ds-card-list/frontend-handoff.md），prototype 用 placeholder 頂替。
 *
 * icon 的值是完整的 icon class（symicon），已對 assets/icon-names.json 確認存在。
 */

return [
    ['label' => '職缺總覽', 'href' => '#', 'icon' => 'icon-home', 'current' => false],
    ['label' => '面試邀約', 'href' => '#', 'icon' => 'icon-mail', 'current' => true],
    ['label' => '我的履歷', 'href' => '#', 'icon' => 'icon-document-text', 'current' => false],
    ['label' => '收藏職缺', 'href' => '#', 'icon' => 'icon-star', 'current' => false],
    ['label' => '帳號設定', 'href' => '#', 'icon' => 'icon-user', 'current' => false],
];
