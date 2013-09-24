#!/bin/bash -e

export SYMFONY_ENV=prod
export SYMFONY_DEBUG=0
SCRIPT_ROOT=`dirname $0`

echo "-----> Update demonstration environment"

echo ""
echo "--> Pull"
git checkout -- composer.lock
git pull

echo ""
echo "--> Clear cache"
rm -rf app/cache/*
#php app/console cache:clear --env=$SYMFONY_ENV

echo ""
echo "--> Install vendors"
php composer.phar install --no-dev --optimize-autoloader

echo ""
echo "--> Update schema, create search index, load fixtures, reindex"
$SCRIPT_ROOT/init-db.sh $SYMFONY_ENV

echo ""
echo "--> Deploy assets"
$SCRIPT_ROOT/assets.sh $SYMFONY_ENV
