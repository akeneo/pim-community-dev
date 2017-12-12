#!/usr/bin/env bash

bin/console --env=prod cache:clear --no-warmup
bin/console --env=dev cache:clear --no-warmup
bin/console --env=behat cache:clear --no-warmup
bin/console --env=prod pim:installer:assets --symlink --clean

yarn install
yarn run webpack
