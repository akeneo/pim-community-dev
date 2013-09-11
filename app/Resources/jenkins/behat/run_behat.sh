#!/bin/bash

#
# Execute Behat feature sequentially in a separate execution context
# in order to avoid high memory consumption
#

usage() {
    echo "Usage: $0 (xdebug|noxdebug) [behat command and options]"
    echo "(feature file name will automatically appended)"
    exit 1;
}

if [ $# -eq 0 ] ; then
    usage
else
    if [ "$1" != 'xdebug' ] && [ "$1" != 'noxdebug' ] ; then
        usage
    fi
fi

FEATURES_DIR=`dirname $0`/../../../../features
XDEBUG_EXTENSION="xdebug.so"

XDEBUG=$1
BEHAT_CMD=`echo $* | sed -e "s/noxdebug//" | sed -e "s/noxdebug//"`

RESULT="OK"

if [ "$XDEBUG" == 'xdebug' ]; then
    PHP_EXTENSION_DIR=`php -i | grep extension_dir | cut -d ' ' -f3`
    if [ ! -f $PHP_EXTENSION_DIR/$XDEBUG_EXTENSION ]; then
       echo Unable to find xdebug extension $PHP_EXTENSION_DIR/$XDEBUG_EXTENSION >&2
       exit 2
    fi
fi

export DISPLAY=:0

FEATURES=`find $FEATURES_DIR/ -name *.feature`
for FEATURE in $FEATURES; do
    FEATURE_NAME=`echo $FEATURE | sed -e 's#^.*/features/\(.*\)$#features/\1#'`
    echo "Executing feature $FEATURE_NAME"
    if [ "$XDEBUG" == 'xdebug' ]; then
        php -d zend_extension=$PHP_EXTENSION_DIR/$XDEBUG_EXTENSION $BEHAT_CMD $FEATURE
    else
        $BEHAT_CMD $FEATURE
    fi

    if [ $? -ne 0 ]; then
        RESULT="KO"
    fi
done

if [ "$RESULT" == KO ]; then
    exit 1;
else
    exit 0;
fi

