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

if [ "$XDEBUG" = 'xdebug' ]; then
    PHP_EXTENSION_DIR=`php -i | grep extension_dir | cut -d ' ' -f3`
    if [ ! -f $PHP_EXTENSION_DIR/$XDEBUG_EXTENSION ]; then
       echo Unable to find xdebug extension $PHP_EXTENSION_DIR/$XDEBUG_EXTENSION >&2
       exit 2
    fi
fi

if [ "$XDEBUG" = 'xdebug' ]; then
    BEHAT_CMD="php -d zend_extension=$PHP_EXTENSION_DIR/$XDEBUG_EXTENSION $BEHAT_CMD"
fi

export DISPLAY=:0

FEATURES_NAMES=""

COUNT=0

# Install the db
pushd .
cd $FEATURES_DIR/..
./install.sh db behat1
./install.sh db behat2
./install.sh db behat3
./install.sh db behat4
popd

PROC_1=0
PROC_2=0
PROC_3=0
PROC_4=0

FEATURES=`find $FEATURES_DIR/ -name *.feature`
for FEATURE in $FEATURES; do
    COUNT=`expr $COUNT + 1`
    
    FEATURE_NAME=`echo $FEATURE | sed -e 's#^.*/features/\(.*\)$#features/\1#'`

    while [ ! -z $FEATURE_NAME ]; do
        ls /proc/$PROC_1 > /dev/null 2>&1
        if [ $? -ne 0 ]; then
            echo "Executing feature $FEATURE_NAME"
            $BEHAT_CMD --profile=jenkins-1 $FEATURE_NAME &
            PROC_1=$!
            FEATURE_NAME=""
        fi

        ls /proc/$PROC_2 > /dev/null 2>&1
        if [ $? -ne 0 ] && [ ! -z $FEATURE_NAME ]; then
            echo "Executing feature $FEATURE_NAME"
            $BEHAT_CMD --profile=jenkins-2 $FEATURE_NAME &
            PROC_2=$!
            FEATURE_NAME=""
        fi

        ls /proc/$PROC_3 > /dev/null 2>&1
        if [ $? -ne 0 ] && [ ! -z $FEATURE_NAME ]; then
            echo "Executing feature $FEATURE_NAME"
            $BEHAT_CMD --profile=jenkins-3 $FEATURE_NAME &
            PROC_3=$!
            FEATURE_NAME=""
        fi

        ls /proc/$PROC_4 > /dev/null 2>&1
        if [ $? -ne 0 ] && [ ! -z $FEATURE_NAME ]; then
            echo "Executing feature $FEATURE_NAME"
            $BEHAT_CMD --profile=jenkins-4 $FEATURE_NAME &
            PROC_4=$!
            FEATURE_NAME=""
        fi
        
        if [ ! -z $FEATURE_NAME ]; then
            sleep 2
        fi
    done
done


