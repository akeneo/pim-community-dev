#!/usr/bin/env bash

set -e
cd "$(dirname "$0")"
cd ./../../

if [ ! -f ./behat.yml ]; then
    cp ./behat.yml.dist ./behat.yml
    sed -i "s/127.0.0.1\//httpd-behat\//g" ./behat.yml
    sed -i "s/127.0.0.1/selenium/g" ./behat.yml
fi


if [ ! -f ./app/config/parameters.yml ]; then
    cp ./app/config/parameters.yml.dist ./app/config/parameters.yml
    sed -i "s/database_host:.*localhost/database_host: mysql/g" ./app/config/parameters.yml
    sed -i "s/localhost: 9200/elasticsearch:9200/g" ./app/config/parameters.yml
fi

if [ ! -f ./app/config/parameters_test.yml ]; then
    cp ./app/config/parameters_test.yml.dist ./app/config/parameters_test.yml
    sed -i "s/database_host:.*localhost/database_host: mysql-behat/g" ./app/config/parameters_test.yml
    sed -i "s/localhost: 9200/elasticsearch:9200/g" ./app/config/parameters_test.yml
    sed -i "s/product_index_name:.*akeneo_pim_product/product_index_name: test_akeneo_pim_product/g" ./app/config/parameters_test.yml
    sed -i "s/product_model_index_name:.*akeneo_pim_product_model/product_model_index_name: test_akeneo_pim_product_model/g" ./app/config/parameters_test.yml
    sed -i "s/product_and_product_model_index_name:.*akeneo_pim_product_and_product_model/product_and_product_model_index_name: test_akeneo_pim_product_and_product_model/g" ./app/config/parameters_test.yml
fi
