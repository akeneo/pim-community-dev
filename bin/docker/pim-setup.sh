#!/usr/bin/env bash

set -e
cd "$(dirname "$0")"
cd ./../../

if [ ! -f ./behat.yml ]; then
    cp ./behat.yml.dist ./behat.yml
    sed -i "s/127.0.0.1\//httpd-behat\//g" ./behat.yml
    sed -i "s/127.0.0.1/selenium/g" ./behat.yml
fi
