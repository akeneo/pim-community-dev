#!/bin/sh
#
# (Re)-installation script for Akeneo PIM
# In default usage:
#   - (re)-create database
#   - index data
#   - create versions
#   - calculate completeness
#   - redeploy assets
#
# This script can be executed several times
#
# You can use the "db" and "assets" arguments to install only "db" or "assets"

set -e
APP_ROOT=`dirname $0`
DEFAULT_ENV="dev"

if [ ! -z $SYMFONY_ENV ]; then
    ENV=$SYMFONY_ENV
else
    ENV=$DEFAULT_ENV
fi

usage()
{
    echo "Usage: $0 [db|assets|all] [--env=ENV]"
    echo "\tdb: will initialize all data"
    echo "\tassets: will initialize assets"
    echo "\tall: will do both"
    exit 1;
}

if [ ! -f $APP_ROOT/app/bootstrap.php.cache ]; then
    echo "It seems that you forget to run composer install inside this directory !" >&2
    exit 2;
fi


# Check usage
if [ $# -eq 0 ] || [ $# -gt 2 ]; then
    usage;
fi

if [ $1 != 'db' ] && [ $1 != 'assets' ] && [ $1 != 'all' ]; then
    usage;
else
    TASK=$1
fi

if [ ! -z $2 ]; then
    ENV=`echo $2 | cut -d '=' -f 2`
    if [ -z $ENV ]; then
        usage;
    fi
fi

# Intialize env
export SYMFONY_ENV=$ENV

if [ -z $SYMFONY_DEBUG ]; then
    if [ $ENV = 'prod' ]; then
        export SYMFONY_DEBUG=0
    else
        export SYMFONY_DEBUG=1
    fi
fi

# Go to the right directory
cd $APP_ROOT

# Execute tasks
if [ $TASK = 'db' ] || [ $TASK = 'all' ]; then
    # Ignoring the case where the DB does not exist yet
    php app/console doctrine:database:drop --force 2>&1 > /dev/null || true
    php app/console doctrine:database:create
    php app/console doctrine:schema:create
    php app/console doctrine:fixture:load --no-interaction
    php app/console oro:acl:load
    php app/console oro:entity-config:update
    php app/console oro:entity-extend:create
    php app/console cache:clear
    php app/console doctrine:schema:update --force
    php app/console oro:search:create-index
    php app/console pim:search:reindex en_US
    php app/console pim:versioning:refresh
    php app/console pim:product:completeness-calculator
fi

if [ $TASK = 'assets' ] || [ $TASK = 'all' ]; then
    php app/console fos:js-routing:dump --target=web/js/routes.js
    php app/console oro:navigation:init
    php app/console assets:install web
    php app/console assetic:dump
    php app/console oro:assetic:dump
    php app/console cache:clear
fi

echo "Done!"
