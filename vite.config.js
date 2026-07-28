// ============================================================
// Pi DS — Vite 設定（僅供本 repo 開發 / 預覽用）
//
// 用途：改 resources/scss/**/*.scss → 預覽頁即時 HMR。
// 預覽頁直接吃 resources/scss/（單一真相源）。
//
// 此 repo 不發布 npm；下游透過 composer 套件取得 resources/scss，
// Vite 僅供本機預覽，不進下游。
// ============================================================

export default {
  // repo 根當 root：resources/、fonts/、assets/、preview-static/ 全在底下，
  // 預覽頁用絕對路徑 /resources/scss/… 即可解到，免 fs.allow hack。
  root: ".",

  server: {
    // 5177 而非 5173：公司其他專案的 Vite 都跑 5173，避免撞號。
    // preview/ 的 Laravel app 用 5178，兩邊可以同時開。
    port: 5177,
    strictPort: true, // 撞號就報錯，不要靜默跳號（跳號會讓下面的 open 開錯）
    open: "/preview-static/index.html", // 啟動預設開預覽目錄頁
  },

  css: {
    preprocessorOptions: {
      // 用 modern-compiler API，避免 Dart Sass 棄用警告。
      scss: { api: "modern-compiler" },
    },
  },

  build: {
    // 預覽頁的靜態輸出（選用）：目錄頁 + 6 張對照頁各自為 input。
    outDir: "dist-preview",
    rollupOptions: {
      input: {
        index: "preview-static/index.html",
        color: "preview-static/color.html",
        type: "preview-static/type.html",
        shadow: "preview-static/shadow.html",
        tokens: "preview-static/tokens.html",
        icons: "preview-static/icons.html",
        // 拆分後的元件頁
        button: "preview-static/button.html",
        form: "preview-static/form.html",
        alert: "preview-static/alert.html",
        callout: "preview-static/callout.html",
        contentSwitcher: "preview-static/content-switcher.html",
        dropdown: "preview-static/dropdown.html",
        pagination: "preview-static/pagination.html",
        notification: "preview-static/notification.html",
        loading: "preview-static/loading.html",
        modal: "preview-static/modal.html",
      },
    },
  },
};
