#!/bin/bash

#
# Execute Behat features in parallel
# The supporting DBs must exists and be available before executing the script.
# Same thing for the Behat profiles
#

XDEBUG_EXTENSION="xdebug.so"
CHECK_WAIT="0.5"
OUTPUT=`mktemp`

usage() {
    echo "Usage: $0 (concurrency) (xdebug|noxdebug) (database_prefix) (profile_prefix) [behat command and options]"
    echo "(feature file name will automatically appended)"
    exit 1;
}

APP_ROOT=`dirname $0`/../../../..
FEATURES_DIR=$APP_ROOT/$FEATURES_DIRECTORY;

while getopts "c:x:d:p:f:" opt; do
  case $opt in
    f)
        FEATURES=$OPTARG
        ;;
    c)
        CONCURRENCY=$OPTARG
        ;;
    x)
        XDEBUG=$OPTARG
        ;;
    d)
        DB_PREFIX=$OPTARG
        ;;
    p)
        PROFILE_PREFIX=$OPTARG
        ;;
    \?)
        usage
    ;;
  esac
done


shift $(expr $OPTIND - 1 );

BEHAT_CMD=$*;

echo "Concurrency = $CONCURRENCY";
echo "Xdebug = $XDEBUG";
echo "Database prefix = $DB_PREFIX";
echo "Profile prefix = $PROFILE_PREFIX";
echo "Behat command = $BEHAT_CMD";
echo "Behat features = $FEATURES";

if [ $XDEBUG != 'xdebug' ] && [ $XDEBUG != 'noxdebug' ] ; then
    echo "Invalid xdebug parameter [$XDEBUG]"
    usage
fi

expr $CONCURRENCY + 0 > /dev/null 2>&1
if [ $? != 0 ]; then
    echo "Invalid concurrency parameter [$CONCURRENCY]"
    usage
fi

ORIGINAL_DB_NAME=`echo $DB_PREFIX | sed -e "s/_$//"`

if [ "$XDEBUG" = 'xdebug' ]; then
    PHP_EXTENSION_DIR=`php -i | grep extension_dir | cut -d ' ' -f3`
    if [ ! -f $PHP_EXTENSION_DIR/$XDEBUG_EXTENSION ]; then
       echo "Unable to find xdebug extension $PHP_EXTENSION_DIR/$XDEBUG_EXTENSION"
       exit 2
    fi
fi

if [ "$XDEBUG" = 'xdebug' ]; then
    BEHAT_CMD="php -d zend_extension=$PHP_EXTENSION_DIR/$XDEBUG_EXTENSION $BEHAT_CMD"
fi

export DISPLAY=:0

# Install the assets and db on all environments
cd $APP_ROOT
for PROC in `seq 1 $CONCURRENCY`; do
    cp app/config/config_behat.yml app/config/config_behat$PROC.yml
    echo "drop database $DB_PREFIX$PROC" | mysql -u root
    echo "create database $DB_PREFIX$PROC" | mysql -u root
    mysqldump -u root $ORIGINAL_DB_NAME | mysql -u root $DB_PREFIX$PROC
    echo "db.dropDatabase()" | mongo $DB_PREFIX$PROC
    eval PID_$PROC=0
done
cd -

if [ -z "${FEATURES// }" ]; then
    FEATURES=`find $FEATURES_DIR/ -name *.feature`
fi

for FEATURE in $FEATURES; do

    FEATURE_NAME=`echo "${FEATURE#$APP_ROOT/}"`

    echo $FEATURE_NAME;

    while [ ! -z "$FEATURE_NAME" ]; do

        for PROC in `seq 1 $CONCURRENCY`; do
            # Make sure there's a feature to process
            if [ ! -z $FEATURE_NAME ]; then

                # Check if processus PID_$PROC is available
                # (/proc/PID should not exist)
                PID_VARNAME=PID_$PROC
                PID="${!PID_VARNAME}"
                export BEHAT_TMPDIR=`mktemp -d`
                ls /proc/$PID > /dev/null 2>&1
                if [ $? -ne 0 ]; then
                    export SYMFONY__DATABASE__NAME=$DB_PREFIX$PROC
                    export SYMFONY__CATALOG__STORAGE__DIR=catalog_$PROC
                    export SYMFONY__ASSET__STORAGE__DIR=asset_$PROC
                    export SYMFONY__MONGODB__DATABASE=$DB_PREFIX$PROC
                    DATE=`date +'%F %T̀'`
                    echo "[$DATE] Executing feature $FEATURE_NAME with proc $PROC" | tee -a $OUTPUT
                    ($BEHAT_CMD --profile=$PROFILE_PREFIX$PROC $FEATURE_NAME 2>&1 | tee -a $OUTPUT) &
                    RESULT=$!
                    eval PID_$PROC=$RESULT
                    FEATURE_NAME=""
                fi
                rm -rf $BEHAT_TMPDIR

            fi
        done

        # There's a feature waiting to be executed, but no processus
        # available. Wait a little bit before checking again
        if [ ! -z $FEATURE_NAME ]; then
            sleep $CHECK_WAIT
        fi
    done
done

# Wait for any remaining processes to finish
for PROC in `seq 1 $CONCURRENCY`; do
    PID_VARNAME=PID_$PROC
    PID="${!PID_VARNAME}"
    wait $PID
done

# Check the output
cat $OUTPUT | grep "failed steps" > /dev/null

if [ $? -eq 0 ]; then
    rm $OUTPUT
    exit 1;
else
    rm $OUTPUT
    exit 0;
fi
