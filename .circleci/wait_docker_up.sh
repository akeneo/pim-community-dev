#!/usr/bin/env bash

set -e

MAX_COUNTER=45
COUNTER=1

echo "Waiting for MySQL server…"
while ! docker-compose exec mysql mysql --protocol TCP -uroot -proot -e "show databases;" > /dev/null 2>&1; do
    sleep 1
    COUNTER=$((${COUNTER} + 1))
    if [ ${COUNTER} -gt ${MAX_COUNTER} ]; then
        echo "We have been waiting for MySQL too long already; failing." >&2
        exit 1
    fi;
done

echo "MySQL server is running!"

COUNTER=1
echo "Waiting for Elasticsearch server…"
while ! docker-compose run --rm php curl http://elastic:changeme@elasticsearch:9200/_cat/health | grep green > /dev/null 2>&1; do
    sleep 1
    COUNTER=$((${COUNTER} + 1))
    if [ ${COUNTER} -gt ${MAX_COUNTER} ]; then
        echo "We have been waiting for Elasticsearch too long already; failing." >&2
        exit 1
    fi;
done

echo "Elasticsearch server is running!"
