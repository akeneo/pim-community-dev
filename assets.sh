#!/bin/bash

if [ "$1" != "" ]; then
  env=$1
else
  env="dev"
fi

php app/console fos:js-routing:dump --target=web/js/routes.js
php app/console oro:navigation:init --env=$env
php app/console assets:install web --env=$env
php app/console assetic:dump --env=$env
php app/console oro:assetic:dump --env=$env
php app/console cache:clear --env=$env


