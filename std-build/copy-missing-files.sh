#!/bin/bash
#
# Script to be launched from the standard distribution to add missing files
#

set -e

DEV_DISTRIB_DIR=$(dirname $0)/..
STANDARD_DISTRIB_DIR=./

cp $DEV_DISTRIB_DIR/CHANGELOG*.md $STANDARD_DISTRIB_DIR
cp $DEV_DISTRIB_DIR/UPGRADE*.md $STANDARD_DISTRIB_DIR
cp -r $DEV_DISTRIB_DIR/upgrades $STANDARD_DISTRIB_DIR

mkdir -p $STANDARD_DISTRIB_DIR/bin $STANDARD_DISTRIB_DIR/public
[ -f $STANDARD_DISTRIB_DIR/bin/console ] || cp $DEV_DISTRIB_DIR/bin/console $STANDARD_DISTRIB_DIR/bin
[ -f $STANDARD_DISTRIB_DIR/public/index.php ] || cp $DEV_DISTRIB_DIR/public/index.php $STANDARD_DISTRIB_DIR/public
[ -f $STANDARD_DISTRIB_DIR/Makefile ] || cp $DEV_DISTRIB_DIR/std-build/Makefile $STANDARD_DISTRIB_DIR
