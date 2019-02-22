#!/usr/bin/env bash

set -e
cd "$(dirname "$0")"
cd ./../../

if [ ! -f ./app/config/parameters.yml ]; then
    cp ./app/config/parameters.yml.dist ./app/config/parameters.yml
    sed -i "s/database_host:.*localhost/database_host: mysql/g" ./app/config/parameters.yml
fi

if [ ! -f ./app/config/parameters_test.yml ]; then
    cp ./app/config/parameters_test.yml.dist ./app/config/parameters_test.yml
    sed -i "s/database_host:.*localhost/database_host:                        mysql-behat/g" ./app/config/parameters_test.yml
fi
