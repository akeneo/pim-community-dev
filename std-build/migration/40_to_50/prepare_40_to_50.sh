#!/bin/bash

set -e

DEV_DISTRIB_DIR=$(dirname $0)/../..
STANDARD_DISTRIB_DIR=./

# We use the same bootstrap.php to load .env file on standard as on CE-dev
cp $DEV_DISTRIB_DIR/config/bootstrap.php $STANDARD_DISTRIB_DIR/config/

# Security configuration cannot be read on CE-dev and overriden in standard. We need to fully copy it
cp $DEV_DISTRIB_DIR/config/packages/security.yml $STANDARD_DISTRIB_DIR/config/packages/security.yml

# Partners are most likely to develop and deploy using local filesystem, not MinIO
cp -r $DEV_DISTRIB_DIR/config/packages/dev $STANDARD_DISTRIB_DIR/config/packages/

# Both production server mode must be available in std
cp -r $DEV_DISTRIB_DIR/config/packages/prod_* $STANDARD_DISTRIB_DIR/config/packages/

# We need a console and FPM entrypoint
cp $DEV_DISTRIB_DIR/bin/console $STANDARD_DISTRIB_DIR/bin/
chmod +x $STANDARD_DISTRIB_DIR/bin/console
cp $DEV_DISTRIB_DIR/public/* $STANDARD_DISTRIB_DIR/public/

# We provide a kernel that loads configuration from the CE dev and override it with the one in standard
cp $DEV_DISTRIB_DIR/std-build/Kernel.php $STANDARD_DISTRIB_DIR/src

# Copy STD configuration
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

# We use the same bootstrap.php to load .env file on standard as on CE-dev
cp $DEV_DISTRIB_DIR/.env $STANDARD_DISTRIB_DIR/

# Prepare database upgrades to run
mkdir -p $STANDARD_DISTRIB_DIR/upgrades/
cp -R $DEV_DISTRIB_DIR/upgrades/* $STANDARD_DISTRIB_DIR/upgrades/

cp $DEV_DISTRIB_DIR/std-build/upgrade/40_to_50/* $STANDARD_DISTRIB_DIR/

printf "Done. \n"
