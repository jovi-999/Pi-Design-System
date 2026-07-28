# ============================================================
# Pi DS preview —— 只給本 repo 開發用的 PHP 容器
#
# 為什麼不用公司 Laradock：
#   本 repo 是 Composer library，出貨的是 SCSS 原始碼 + blade 檔，
#   沒有 build step、沒有資料庫、沒有 queue。專案端只 composer require，
#   永遠不會執行本 repo。Laradock 的 nginx / mysql / redis 全無用途。
#   這個容器唯一的工作是 render blade 給 preview 看。
#
# 分工：PHP 在 container，Vite 在 host（node_modules 已在 host，
#      省一個 node container；hot file 走 bind mount 共享）。
# ============================================================
FROM php:8.2-cli

# 官方 php image 未內建 mbstring，但 Laravel 必需（編譯需 libonig-dev）。
# zip 給 composer 解壓縮套件用（缺了會 fallback 到外部 unzip 指令，較慢）。
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-install mbstring zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 對齊 docker-compose.yml：整個 repo 掛在 /var/www，preview app 在其下
WORKDIR /var/www/preview
