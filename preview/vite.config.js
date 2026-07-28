// ============================================================
// Pi DS preview —— Vite 設定
//
// 跑在 host（不在容器內）：node_modules 已經在 host，省一個 node
// container；hot file（public/hot）走 bind mount 給容器內的 PHP 讀。
//
// 已移除 Laravel 12 skeleton 預設的 Tailwind：Tailwind 的 preflight
// 會跟 Pi DS 的 reset / @layer pi 打架，preview 必須只反映 Pi DS 本身
// 的樣式，否則「preview 看起來對、貼進專案走鐘」的失敗會被引進來。
// ============================================================
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            // 改套件本體的 blade 元件也要觸發 full reload（../resources/views
            // 是 repo 根的實體路徑，不是 vendor/ 的 symlink）
            refresh: [
                'resources/views/**',
                'routes/**',
                '../resources/views/**',
            ],
        }),
    ],

    css: {
        preprocessorOptions: {
            // 對齊 repo 根的 vite.config.js，避免 Dart Sass 棄用警告
            scss: { api: 'modern-compiler' },
        },
    },

    server: {
        host: '127.0.0.1',
        // 5178：公司其他專案的 Vite 走 5173，repo 根的 preview-static 走 5177。
        // strictPort 讓撞號直接報錯 —— 靜默跳號會寫錯 public/hot，
        // 容器內的 PHP 就會指向一個沒東西的 port，症狀是「頁面沒樣式」。
        port: 5178,
        strictPort: true,
        watch: {
            ignored: [
                '**/storage/framework/views/**',
                // vendor/pi-tw/pi-design-system 是指回 repo 根的 symlink，
                // repo 根底下又有 preview/ —— 不排除會讓 watcher 無限遞迴
                '**/vendor/**',
            ],
        },
    },
});
