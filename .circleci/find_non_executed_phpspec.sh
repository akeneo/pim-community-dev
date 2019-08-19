#!/bin/sh

set -eu

EXECUTED_PHPSPEC_FILE=$(mktemp --suffix "executed")
EXISTING_PHPSPEC_FILE=$(mktemp --suffix "existing")
cat var/tests/phpspec/specs.xml | grep -Po '(?<=classname\=").*(?=\" status)' | sort | uniq > $EXECUTED_PHPSPEC_FILE
find src tests -not -path "src/Akeneo/ReferenceEntity/*" -not -path "src/Akeneo/AssetManager/*" -name "*Spec.php" -type f | xargs grep "^namespace" |  tr "/" "\n" | grep namespace | sed -e 's/\([^/:]\+\):\([^:;]\+\);$/\2\\\1/' | cut -d " " -f 2 | cut -d "." -f 1 | sort | uniq > $EXISTING_PHPSPEC_FILE

echo "Maybe you have some diff between existing PHPSpec and those which are executed, here is the list:"
diff $EXECUTED_PHPSPEC_FILE $EXISTING_PHPSPEC_FILE
