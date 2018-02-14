#!/usr/bin/env bash

rm -rf ./var/cache
rm -rf ./web/js
rm -rf ./web/css
bin/console --env=prod pim:installer:assets --symlink --clean

yarn run webpack-dev
