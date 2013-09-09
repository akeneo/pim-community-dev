#!/bin/bash

FEATURES_DIR=`dirname $0`/../../../../features
BEHAT_COVERAGE=`dirname $0`/behat-coverage

FEATURES=`find $FEATURES_DIR/ -name *.feature`
RESULT="OK"

PHP_EXTENSION_DIR=`php -i | grep extension_dir | cut -d ' ' -f3`

export DISPLAY=:0

for FEATURE in $FEATURES; do
    echo "Executing feature $FEATURE"
    php -d zend_extension=$PHP_EXTENSION_DIR/xdebug.so $BEHAT_COVERAGE $FEATURE --profile=jenkins-coverage --ansi --tags=~skip --fprogress
    if [ $? -ne 0 ]; then
        RESULT="KO"
    fi
done

if [ $RESULT == KO ]; then
    exit 1;
else
    exit 0;
fi

