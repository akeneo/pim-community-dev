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

ORO_BUNDLE_PATH="vendor/oro/platform/src/Oro/Bundle/"
ORO_FIXTURE_BUNDLES="
    AddressBundle/DataFixtures
    EmailBundle/DataFixtures
    NotificationBundle/DataFixtures
    OrganizationBundle/DataFixtures
    SecurityBundle/DataFixtures
    UserBundle/DataFixtures
    TestFrameworkBundle/Fixtures
    WorkflowBundle/DataFixtures
"
ORO_FIXTURES=`echo $ORO_FIXTURE_BUNDLES | sed -e "s# # --fixtures=$ORO_BUNDLE_PATH#g" -e "s#^# --fixtures=$ORO_BUNDLE_PATH#"`

PIM_FIXTURE_PATHS="
    src/Pim/Bundle/InstallerBundle/DataFixtures
    src/Pim/Bundle/UserBundle/DataFixtures
    src/Pim/Bundle/CustomEntityBundle/DataFixtures
    src/Pim/Bundle/DemoBundle/DataFixtures
"
PIM_FIXTURES=`echo $PIM_FIXTURE_PATHS | sed -e "s# # --fixtures=#g" -e "s#^# --fixtures=#"`

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
    echo
    echo "\tBy default, ENV is dev"
    exit 1;
}

if [ ! -f $APP_ROOT/app/bootstrap.php.cache ]; then
    echo "It seems that you forget to run composer install inside this directory!" >&2
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
    if [ $ENV = 'prod' ] || [ $ENV = 'behat' ]; then
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
    php app/console doctrine:schema:drop --force --full-database > /dev/null 2>&1 || true
    php app/console doctrine:database:drop --force > /dev/null 2>&1 || true
    php app/console doctrine:database:create
    php app/console doctrine:schema:create
    php app/console cache:clear
    echo "Loading ORO fixtures"
    php app/console doctrine:fixtures:load $ORO_FIXTURES --no-interaction
    php app/console oro:entity-config:init
    php app/console oro:entity-extend:init
    php app/console oro:entity-extend:update-config
    php app/console doctrine:schema:update --force
    if [ $ENV != 'behat' ]; then
        echo "Loading PIM fixtures"
        php app/console doctrine:fixtures:load $PIM_FIXTURES --no-interaction --append
    fi
    php app/console oro:search:create-index
    php app/console pim:search:reindex en_US
    php app/console pim:versioning:refresh
    php app/console doctrine:query:sql "ANALYZE TABLE pim_product_value" > /dev/null 2>&1 || true
    php app/console doctrine:query:sql "ANALYZE TABLE pim_icecatdemo_product_value" > /dev/null 2>&1 || true
    php app/console pim:completeness:calculate
fi

if [ $TASK = 'assets' ] || [ $TASK = 'all' ]; then
    php app/console fos:js-routing:dump --target=web/js/routes.js
    php app/console oro:navigation:init
    php app/console assets:install web
    php app/console assetic:dump
    php app/console oro:assetic:dump
    php app/console oro:translation:dump
    php app/console oro:localization:dump
    php app/console cache:clear
fi

echo "Done!"
