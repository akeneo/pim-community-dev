#!/bin/bash
#
# Generate a standard edition on the provided directory
#

set -e

if [ ! $# -eq 1 ]; then
    echo "Usage: $0 <target-directory>" >&2
    exit 1;
fi

TARGET_DIR="$1"
if [ ! -d "$1" ] || [ ! -w "$1" ]; then
    echo "Directory $1 does not exist or is not writable" >&2
    exit 2
fi


SRC_DIR=$(dirname $0)/..

cp $SRC_DIR/std-build/composer.json $TARGET_DIR/

cp $SRC_DIR/Dockerfile $TARGET_DIR/
cp -r $SRC_DIR/docker $TARGET_DIR/
cp -r $SRC_DIR/docker-compose.yml $TARGET_DIR/
