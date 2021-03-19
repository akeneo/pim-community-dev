#!/bin/bash
#
# Script to be launched from the standard distribution to add missing files
# add installation files.
#
# This script is only meant for installation from scratch, not for migration
#

set -e

DEV_DISTRIB_DIR=$(dirname $0)/..
STANDARD_DISTRIB_DIR=./

# Step: Install and upgrade
###########################

# Update doctrine migratation files
mkdir -p $STANDARD_DISTRIB_DIR/upgrades/
cp -R $DEV_DISTRIB_DIR/upgrades/* $STANDARD_DISTRIB_DIR/upgrades/

# Dot env file
cp $DEV_DISTRIB_DIR/.env $STANDARD_DISTRIB_DIR/

# Akeneo managed Kernel
[ -d $STANDARD_DISTRIB_DIR/src ] && cp $DEV_DISTRIB_DIR/std-build/Kernel.php


# Step: Install
###########################
[ -d $STANDARD_DISTRIB_DIR/src ] && echo "src/ directory already exists. Not preparing the directory content." && exit 0

# Required directories
mkdir -p $STANDARD_DISTRIB_DIR/src \
         $STANDARD_DISTRIB_DIR/bin \
         $STANDARD_DISTRIB_DIR/public \
         $STANDARD_DISTRIB_DIR/config/packages/dev \
         $STANDARD_DISTRIB_DIR/config/packages/prod \
         $STANDARD_DISTRIB_DIR/config/services \
         $STANDARD_DISTRIB_DIR/docker \
         $STANDARD_DISTRIB_DIR/docker/initdb.d

# Provides the Apache and FPM configuration to run the PIM from Docker
cp $DEV_DISTRIB_DIR/docker/wait_docker_up.sh $STANDARD_DISTRIB_DIR/docker/
cp $DEV_DISTRIB_DIR/docker/httpd.conf $STANDARD_DISTRIB_DIR/docker/
cp $DEV_DISTRIB_DIR/docker/akeneo.conf $STANDARD_DISTRIB_DIR/docker/

# We use the same bootstrap.php to load .env file on standard as on CE-dev
cp $DEV_DISTRIB_DIR/config/bootstrap.php $STANDARD_DISTRIB_DIR/config/

# Security configuration cannot be read on CE-dev and overriden in standard. We need to fully copy it
cp $DEV_DISTRIB_DIR/config/packages/security.yml $STANDARD_DISTRIB_DIR/config/packages/security.yml

# We need a console and FPM entrypoint
cp $DEV_DISTRIB_DIR/bin/console $STANDARD_DISTRIB_DIR/bin/
chmod +x $STANDARD_DISTRIB_DIR/bin/console
cp $DEV_DISTRIB_DIR/public/* $STANDARD_DISTRIB_DIR/public/

# This is a skeleton file to encourage them to put their bundles inside it
cp $DEV_DISTRIB_DIR/std-build/bundles.php $STANDARD_DISTRIB_DIR/config

# Uses same docker compose than the CE
cp $DEV_DISTRIB_DIR/docker-compose.yml $STANDARD_DISTRIB_DIR/docker-compose.yml

# Usable example Makefile
cp $DEV_DISTRIB_DIR/std-build/Makefile $STANDARD_DISTRIB_DIR/Makefile

# Front dependencies using workspace to depends on CE-dev and inherits its deps
cp $DEV_DISTRIB_DIR/std-build/package.json $STANDARD_DISTRIB_DIR/package.json
cp $DEV_DISTRIB_DIR/yarn.lock $STANDARD_DISTRIB_DIR/yarn.lock

# Needed to define the loader path to target the CE-dev
cp $DEV_DISTRIB_DIR/std-build/tsconfig.json $STANDARD_DISTRIB_DIR/tsconfig.json

# Needed to define Elasticsearch mapping file location inside CE-dev
cp $DEV_DISTRIB_DIR/std-build/services.yml $STANDARD_DISTRIB_DIR/config/services/

# Skeleton .env file
cp $DEV_DISTRIB_DIR/.gitignore $STANDARD_DISTRIB_DIR/
