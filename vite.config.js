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
  // repo 根當 root：resources/、fonts/、assets/、preview/ 全在底下，
  // 預覽頁用絕對路徑 /resources/scss/… 即可解到，免 fs.allow hack。
  root: ".",

  server: {
    port: 5173,
    open: "/preview/index.html", // 啟動預設開預覽目錄頁
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
        index: "preview/index.html",
        color: "preview/color.html",
        type: "preview/type.html",
        shadow: "preview/shadow.html",
        tokens: "preview/tokens.html",
        icons: "preview/icons.html",
        // 拆分後的元件頁
        button: "preview/button.html",
        form: "preview/form.html",
        alert: "preview/alert.html",
        callout: "preview/callout.html",
        contentSwitcher: "preview/content-switcher.html",
        dropdown: "preview/dropdown.html",
        pagination: "preview/pagination.html",
        notification: "preview/notification.html",
        loading: "preview/loading.html",
        modal: "preview/modal.html",
      },
    },
  },
};
