{{--
    Pi DS — Pagination v3

    對應 resources/scss/components/_pagination.scss。
    結構照 preview-static/pagination.html（前綴是 iw_ 不是 gl_，這是對齊
    生產環境的既有命名，不要「順手改成 gl_」——那是 breaking change）：

      <div class="iw_pagination-outer-v3">
        <div class="iw_pagination-wrap">
          <a class="gl_page-link is-disabled" aria-label="上一頁"><i class="icon icon-arrow-left"></i></a>
          <ul class="gl_pagination-group">
            <li class="page iw_active"><a class="gl_page-link">1</a></li>
            …
          </ul>
          <a class="gl_page-link" aria-label="下一頁"><i class="icon icon-arrow-right"></i></a>
        </div>
      </div>

    當前頁的深色實心是 `.iw_active .gl_page-link`，所以 .iw_active 要掛在
    <li> 上而不是 <a> 上。
--}}
@props([
    'current' => 1,          // 當前頁
    'last' => 1,             // 最後一頁
    'urlTemplate' => '?page=:page', // :page 會被頁碼取代
])

@php
    $current = max(1, (int) $current);
    $last = max(1, (int) $last);

    if ($current > $last) {
        throw new InvalidArgumentException(
            "x-pi::pagination 的 current（{$current}）不可大於 last（{$last}）"
        );
    }

    $url = fn (int $page) => str_replace(':page', (string) $page, $urlTemplate);
@endphp

<div {{ $attributes->class(['iw_pagination-outer-v3']) }}>
    <div class="iw_pagination-wrap">
        <a
            @class(['gl_page-link', 'is-disabled' => $current <= 1])
            href="{{ $url(max(1, $current - 1)) }}"
            data-page="{{ max(1, $current - 1) }}"
            aria-label="上一頁"
        ><i class="icon icon-arrow-left"></i></a>

        <ul class="gl_pagination-group">
            @for ($page = 1; $page <= $last; $page++)
                <li @class(['page', 'iw_active' => $page === $current])>
                    <a class="gl_page-link" href="{{ $url($page) }}" data-page="{{ $page }}">{{ $page }}</a>
                </li>
            @endfor
        </ul>

        <a
            @class(['gl_page-link', 'is-disabled' => $current >= $last])
            href="{{ $url(min($last, $current + 1)) }}"
            data-page="{{ min($last, $current + 1) }}"
            aria-label="下一頁"
        ><i class="icon icon-arrow-right"></i></a>
    </div>
</div>
