#!/usr/bin/env bash

docker-compose exec akeneo php -d memory_limit=3G /usr/local/bin/composer update
