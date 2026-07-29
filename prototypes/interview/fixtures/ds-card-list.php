<?php

/**
 * Fixture：ds-card-list 頁主欄的卡片列表。
 *
 * 這份 fixture 的 key 結構就是給後端的資料契約 —— 欄位意涵見
 * .scratch/ds-card-list/backend-handoff.md 的「資料欄位清單」。
 *
 * image 的值刻意用 data URI 的灰塊佔位圖，理由有兩個：
 *   1. prototype 不該依賴外部圖床 —— 圖掛掉時 PM 會誤以為是版面壞了
 *   2. 正式資料是後端給的圖片 URL，佔位圖只是撐出圖片的框，
 *      不是設計決策，所以這裡的 hex 不是 token（Pi DS 的 basic 色階實際值）
 */

/** 產生指定底色的佔位圖（240×180，卡片縮圖的比例）。 */
$placeholderImage = static fn (string $fillHex): string => 'data:image/svg+xml;base64,' . base64_encode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="240" height="180">'
    . '<rect width="240" height="180" fill="' . $fillHex . '"/>'
    . '</svg>'
);

return [
    [
        'id' => 101,
        'title' => '前端工程師 — 第二輪技術面試',
        // 內文固定 2 行（超出由 -webkit-line-clamp 截斷），所以這裡刻意給長字串
        'excerpt' => '與技術主管進行 60 分鐘的系統設計討論，會請你說明過去專案的取捨與權衡。面試前請先看過我們寄的作業說明。',
        'image' => $placeholderImage('#F1F3F5'),
        'imageAlt' => '前端工程師職缺縮圖',
        'action' => ['label' => '查看細節', 'href' => '#'],
    ],
    [
        'id' => 102,
        'title' => '產品設計師 — 作品集審閱',
        'excerpt' => '設計團隊會一起看你的作品集，重點在流程與決策依據而不是視覺完成度。請準備 2 到 3 個代表性案例。',
        'image' => $placeholderImage('#F9FAFB'),
        'imageAlt' => '產品設計師職缺縮圖',
        'action' => ['label' => '查看細節', 'href' => '#'],
    ],
    [
        'id' => 103,
        'title' => '資料分析師 — 主管面談',
        'excerpt' => '與資料團隊主管面談，會聊到你熟悉的工具鏈與過去做過的指標定義。這一輪不含實作測驗。',
        'image' => $placeholderImage('#C4C7C9'),
        'imageAlt' => '資料分析師職缺縮圖',
        'action' => ['label' => '查看細節', 'href' => '#'],
    ],
];
