#!/usr/bin/env bash
set -e

echo "[PHP Entrypoint] Starting in development mode..."

# vendor がない場合、自動で composer install
if [ ! -d "/var/www/html/vendor" ] && [ -f "/var/www/html/composer.json" ]; then
  echo "vendor/ not found. Running composer install..."
  composer install --no-interaction --prefer-dist --no-progress
else
  echo "vendor/ already exists. Skipping composer install."
fi

# composer.lock がある場合は dump-autoload を実行（開発向け）
if [ -f "/var/www/html/composer.lock" ]; then
  echo "Running composer dump-autoload..."
  composer dump-autoload --optimize
fi

# 権限を調整（開発環境向け。必要なければコメントアウト）
chown -R www-data:www-data /var/www/html || true

# 最後に php-fpm を起動
echo "[PHP Entrypoint] Launching php-fpm..."
exec docker-php-entrypoint php-fpm
