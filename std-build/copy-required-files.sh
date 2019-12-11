#!/bin/bash
#
# Script to be launched from the standard distribution to add missing files
#

set -e

DEV_DISTRIB_DIR=$(dirname $0)/..
STANDARD_DISTRIB_DIR=./

mkdir -p $STANDARD_DISTRIB_DIR/src \
         $STANDARD_DISTRIB_DIR/bin \
         $STANDARD_DISTRIB_DIR/public \
         $STANDARD_DISTRIB_DIR/config/packages \
         $STANDARD_DISTRIB_DIR/config/services \
         $STANDARD_DISTRIB_DIR/docker

cp $DEV_DISTRIB_DIR/CHANGELOG*.md $STANDARD_DISTRIB_DIR
cp $DEV_DISTRIB_DIR/UPGRADE*.md $STANDARD_DISTRIB_DIR
cp -r $DEV_DISTRIB_DIR/upgrades $STANDARD_DISTRIB_DIR
cp $DEV_DISTRIB_DIR/config/bootstrap.php $STANDARD_DISTRIB_DIR/config/
cp $DEV_DISTRIB_DIR/bin/console $STANDARD_DISTRIB_DIR/bin/
cp $DEV_DISTRIB_DIR/public/index.php $STANDARD_DISTRIB_DIR/public/
cp $DEV_DISTRIB_DIR/std-build/Kernel.php $STANDARD_DISTRIB_DIR/src
cp $DEV_DISTRIB_DIR/.env $STANDARD_DISTRIB_DIR/

# The following files are only copied if the destination does not exist
cp --no-clobber $DEV_DISTRIB_DIR/config/packages/security.yml $STANDARD_DISTRIB_DIR/config/packages/security.yml
cp --no-clobber $DEV_DISTRIB_DIR/std-build/Makefile $STANDARD_DISTRIB_DIR/Makefile
cp --no-clobber $DEV_DISTRIB_DIR/std-build/bundles.php $STANDARD_DISTRIB_DIR/config
cp --no-clobber $DEV_DISTRIB_DIR/std-build/package.json $STANDARD_DISTRIB_DIR/package.json
cp --no-clobber $DEV_DISTRIB_DIR/yarn.lock $STANDARD_DISTRIB_DIR/yarn.lock
cp --no-clobber $DEV_DISTRIB_DIR/std-build/tsconfig.json $STANDARD_DISTRIB_DIR/tsconfig.json
cp --no-clobber -r $DEV_DISTRIB_DIR/docker/* $STANDARD_DISTRIB_DIR/docker/
cp --no-clobber $DEV_DISTRIB_DIR/.gitignore $STANDARD_DISTRIB_DIR/
cp --no-clobber $DEV_DISTRIB_DIR/std-build/services.yml $STANDARD_DISTRIB_DIR/config/services/

[ ! -f $STANDARD_DISTRIB_DIR/docker-composer.yml ] | sed -e 's#akeneo/pim-dev/php:7.3#akeneo/pim-php-dev:4.0#g' $DEV_DISTRIB_DIR/docker-compose.yml > $STANDARD_DISTRIB_DIR/docker-compose.yml
