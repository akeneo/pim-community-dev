#!/bin/bash
#
# Script to be launched from the standard distribution to add missing files
#

set -e

DEV_DISTRIB_DIR=$(dirname $0)/..
STANDARD_DISTRIB_DIR=./

mkdir -p $STANDARD_DISTRIB_DIR/src $STANDARD_DISTRIB_DIR/bin $STANDARD_DISTRIB_DIR/public $STANDARD_DISTRIB_DIR/config/packages/prod

cp $DEV_DISTRIB_DIR/CHANGELOG*.md $STANDARD_DISTRIB_DIR
cp $DEV_DISTRIB_DIR/UPGRADE*.md $STANDARD_DISTRIB_DIR
cp -r $DEV_DISTRIB_DIR/upgrades $STANDARD_DISTRIB_DIR
cp $DEV_DISTRIB_DIR/config/bootstrap.php $STANDARD_DISTRIB_DIR/config/
cp $DEV_DISTRIB_DIR/bin/console $STANDARD_DISTRIB_DIR/bin/
cp $DEV_DISTRIB_DIR/public/index.php $STANDARD_DISTRIB_DIR/public/
cp $DEV_DISTRIB_DIR/std-build/Kernel.php $STANDARD_DISTRIB_DIR/src

[ -f $STANDARD_DISTRIB_DIR/config/packages/prod/oneup_flysystem.yml ] || cp $DEV_DISTRIB_DIR/config/packages/prod_onprem_paas/oneup_flysystem.yml $STANDARD_DISTRIB_DIR/config/packages/prod/oneup_flysystem.yml
