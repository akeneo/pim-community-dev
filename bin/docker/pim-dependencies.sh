#!/usr/bin/env bash
set -e

docker-compose exec fpm php -d memory_limit=3G /usr/local/bin/composer install
docker-compose run --rm node yarn -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome install
