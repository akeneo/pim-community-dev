#!/bin/bash -e

export SYMFONY_ENV=prod
export SYMFONY_DEBUG=0

echo "-----> Update demonstration environment"

echo ""
echo "--> Pull"
git checkout -- composer.lock
git pull

echo ""
echo "--> Clear cache"
php app/console cache:clear --env=$SYMFONY_ENV

echo ""
echo "--> Install vendors"
php composer.phar install --no-dev --optimize-autoloader

echo ""
echo "--> Update schema, create search index, load fixtures, reindex"
php app/console doctrine:database:drop --force
php app/console doctrine:database:create
php app/console doctrine:schema:update --force
php app/console doctrine:fixtures:load --no-debug --no-interaction

echo "--> Load acl and navigation"
php app/console oro:acl:load
php app/console oro:navigation:init

echo ""
echo "--> Create search index and reindex"
php app/console oro:search:create-index
php app/console oro:search:index
php app/console pim:search:reindex en_US


echo ""
echo "--> Deploy assets"
php app/console fos:js-routing:dump --target=web/js/routes.js
php app/console assets:install web
php app/console assetic:dump
php app/console oro:assetic:dump

