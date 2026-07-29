@piFragment([
    'target' => 'project-a:resources/views/members/index.blade.php',
    'slot' => 'member-list.toolbar',
    'host' => 'project-a/_hosts/members-index.html',
])

@piFixture($toolbar, 'member-list-toolbar')

{{--
    日期範圍用兩個原生 date input（`<x-pi::form-control type="date">`）而不是
    單一範圍選擇器 —— 設計系統沒有任何日期元件，單欄式範圍選擇器需要 JS 日曆
    彈窗，屬真缺件。PM 已確認採兩欄式。

    ⚠️ 已提報的元件缺口（第四次出現，不重複提報）：欄位標籤沒有元件，
    這裡沿用 salary-report 的疊法（`fz-title-sm fz-tit` + <label for>）。
    見 .scratch/salary-report/frontend-handoff.md 的 field-label 提議。

    自訂樣式 11 行，未超過 CLAUDE.md 第 3 條的 30 行上限。
--}}
<style>
    .pa-toolbar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0 16px;
    }
    .pa-toolbar__dates { display: flex; align-items: flex-end; gap: 8px; }
    .pa-toolbar__field { display: flex; flex-direction: column; gap: 6px; }
    /* .gl_form-group 是 width:100%，工具列裡要收成固定寬才不會把匯出鈕推掉 */
    .pa-toolbar__field .gl_form-group { width: 168px; }
</style>

<div class="pa-toolbar">
    <div class="pa-toolbar__dates">
        <div class="pa-toolbar__field">
            <label class="fz-title-sm fz-tit" for="dateFrom">起始日</label>
            <x-pi::form-control
                type="date"
                id="dateFrom"
                name="dateFrom"
                :value="$toolbar['dateFrom']"
            />
        </div>

        <div class="pa-toolbar__field">
            <label class="fz-title-sm fz-tit" for="dateTo">結束日</label>
            <x-pi::form-control
                type="date"
                id="dateTo"
                name="dateTo"
                :value="$toolbar['dateTo']"
            />
        </div>
    </div>

    <x-pi::button variant="outline" tone="basic" icon-left="icon-download">匯出</x-pi::button>
</div>
