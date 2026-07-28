<?php

/**
 * 會員狀態選項。
 *
 * 這份 fixture 的 key 結構就是給後端的資料契約 —— controller 要傳出
 * 同樣結構（value / label）的陣列，前端 blade 一個字都不用改。
 */

return [
    ['value' => 'active', 'label' => '啟用中'],
    ['value' => 'suspended', 'label' => '已停權'],
    ['value' => 'pending', 'label' => '待驗證'],
];
