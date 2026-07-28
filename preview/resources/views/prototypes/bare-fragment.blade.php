{{--
    Fragment 無法注入宿主快照時的退回畫面。

    片段照舊顯示（不要讓人以為 render 失敗），但把原因寫在最上面 ——
    靜默 fallback 會讓人以為是 host 壞了而去改快照，方向就錯了。
--}}
@extends('pi::layouts.preview')

@section('title', 'Fragment（無宿主）')

@section('content')
<div style="max-width: 960px; margin: 24px auto; padding: 0 24px;">
    <div class="pv-error fz-body-sm" style="margin-bottom: 24px;">
        <strong>未注入宿主快照</strong><br>
        {{ $reason }}
    </div>

    {!! $fragmentHtml !!}
</div>
@endsection
