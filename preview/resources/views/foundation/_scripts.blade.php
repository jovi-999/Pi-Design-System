{{--
    Foundation 頁的兩個行為，共用一份：

    1. 把 <code data-token="--x"> 填成瀏覽器解析後的實際值
    2. 點 token 名稱／icon class 複製到剪貼簿
    3. 搜尋框過濾 [data-search] 的列（若該頁有搜尋框）

    刻意不進 app.js —— 只有 foundation 頁需要，放在這裡看得到它服務誰。
--}}
<script>
    (() => {
        const rootStyle = getComputedStyle(document.documentElement);

        // ---------- 1. 填實際值 ----------
        for (const el of document.querySelectorAll('[data-token]')) {
            const value = rootStyle.getPropertyValue(el.dataset.token).trim();
            // 空字串＝這個 token 沒有真的被載入的 CSS 宣告，要看得見而不是顯示空白
            el.textContent = value || '⚠️ 未定義';
            if (!value) el.classList.add('pv-undefined');
        }

        // ---------- 2. 點擊複製 ----------
        document.addEventListener('click', async (event) => {
            const trigger = event.target.closest('[data-copy]');
            if (!trigger) return;

            try {
                await navigator.clipboard.writeText(trigger.dataset.copy);
                const original = trigger.textContent;
                trigger.textContent = '已複製';
                setTimeout(() => { trigger.textContent = original; }, 900);
            } catch {
                // 剪貼簿 API 在非 https / 無權限時會擋，不要靜默失敗
                trigger.title = '複製失敗，請手動選取';
            }
        });

        // ---------- 3. 搜尋 ----------
        const search = document.querySelector('[data-search-input]');
        if (search) {
            const rows = [...document.querySelectorAll('[data-search]')];
            const counter = document.querySelector('[data-search-count]');

            const apply = () => {
                const q = search.value.trim().toLowerCase();
                let shown = 0;

                for (const row of rows) {
                    const hit = q === '' || row.dataset.search.toLowerCase().includes(q);
                    row.hidden = !hit;
                    if (hit) shown++;
                }

                if (counter) counter.textContent = shown;
            };

            search.addEventListener('input', apply);
            apply();
        }
    })();
</script>
