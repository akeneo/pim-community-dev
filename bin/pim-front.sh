#!/usr/bin/env bash

rm -rf ./var/cache
rm -rf ./public/js
rm -rf ./public/css
bin/console --env=prod pim:installer:assets --symlink --clean

yarn run less
yarn run webpack-dev
yarn run webpack-test
