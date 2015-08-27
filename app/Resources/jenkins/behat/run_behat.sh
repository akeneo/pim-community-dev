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
    echo "Usage: $0 -c concurrency -x xdebug|noxdebug -d database_prefix -p profile_prefix [-f behat_feature1:behat_feature2:behat_dir1] [-h] -b behat_command_and_options"
    echo "  -f : Force features file or directory. If not specified, all features are executed"
    exit 1;
}

APP_ROOT=`dirname $0`/../../../..
FEATURES_DIR=$APP_ROOT/$FEATURES_DIRECTORY

IS_OPTION='echo $1 | grep "^-" > /dev/null'

while [ "$1" != "" ]; do
    case $1 in
        -x)     shift
                eval $IS_OPTION || XDEBUG=$1
                [ -n "$XDEBUG" ] && shift
                ;;


        -c)     shift
                eval $IS_OPTION || CONCURRENCY=$1
                [ -n "$CONCURRENCY" ] && shift
                ;;

        -f)     shift
                eval $IS_OPTION || FORCED_FEATURES=$1
                [ -n "$FORCED_FEATURES" ] && shift
                ;;

        -d)     shift
                eval $IS_OPTION || DB_PREFIX=$1
                [ -n "$DB_PREFIX" ] && shift
                ;;

        -p)     shift
                eval $IS_OPTION || PROFILE_PREFIX=$1
                [ -n "$PROFILE_PREFIX" ] && shift
                ;;

        -b)     shift
                eval $IS_OPTION || BEHAT_CMD=$1
                [ -n "$BEHAT_CMD" ] && shift
                ;;

        *|-h)   usage
                ;;
    esac
done

FEATURES=""
for FORCED_FEATURE in $(echo $FORCED_FEATURES | sed -e "s#:# #g"); do

    if (echo $FORCED_FEATURE | grep "\.feature$" > /dev/null); then
        # Feature file provided
        FEATURES="$FEATURES $FEATURES_DIR/$FORCED_FEATURE"
    else
        # Directory provided
        FEATURES="$FEATURES $(find $FEATURES_DIR/$FORCED_FEATURE -name *.feature)"
    fi
done

if [ -z "$XDEBUG" ] || [ $XDEBUG != 'xdebug' ] && [ $XDEBUG != 'noxdebug' ] ; then
    echo "Missing or Invalid xdebug parameter [$XDEBUG]"
    usage
fi

if [ -z "$PROFILE_PREFIX" ]; then
    echo "Missing profile prefix parameter"
    usage
fi

if [ -z "$DB_PREFIX" ]; then
    echo "Missing DB prefix parameter"
    usage
fi

if [ -z "$BEHAT_CMD" ]; then
    echo "Missing Behat command and options"
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

if [ -z "$FEATURES" ]; then
    FEATURES=$(find $FEATURES_DIR/ -name *.feature)
fi

for FEATURE in $FEATURES; do

    FEATURE_NAME=`echo "${FEATURE#$APP_ROOT/}"`

    while [ ! -z $FEATURE_NAME ]; do

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

