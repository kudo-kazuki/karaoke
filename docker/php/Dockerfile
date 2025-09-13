FROM php:8.3-fpm

ENV TZ=Asia/Tokyo

# 必須パッケージ & PHP拡張
RUN set -eux; \
  apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql zip \
  && docker-php-ext-install -j"$(nproc)" opcache \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer を公式手順で導入（署名検証つき：curl版）
RUN set -eux; \
EXPECTED_SIGNATURE="$(curl -fsSL https://composer.github.io/installer.sig)"; \
curl -fsSL -o composer-setup.php https://getcomposer.org/installer; \
ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then \
  echo 'ERROR: Invalid installer signature' >&2; \
  rm -f composer-setup.php; \
  exit 1; \
fi; \
php composer-setup.php --install-dir=/usr/bin --filename=composer; \
rm -f composer-setup.php; \
composer --version

WORKDIR /var/www/html

# （任意）php.ini を上書きする場合
COPY ./php.ini /usr/local/etc/php/php.ini

# （任意）entrypoint を使う場合
# COPY ./php/entrypoint.sh /usr/local/bin/entrypoint.custom.sh
# RUN chmod +x /usr/local/bin/entrypoint.custom.sh
# ENTRYPOINT ["/usr/local/bin/entrypoint.custom.sh"]

EXPOSE 9000
