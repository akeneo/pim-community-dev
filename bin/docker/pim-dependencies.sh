#!/usr/bin/env bash
set -e

docker-compose exec fpm php -d memory_limit=4G /usr/local/bin/composer update
docker-compose run --rm -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 node yarn install
