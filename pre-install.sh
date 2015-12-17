#!/bin/bash
echo "Checking MongoDB extension"
php -r "extension_loaded('mongo') ? exit(0):exit(1);"
if [[ $? > 0 ]]; then
    php ./composer.phar --no-update remove doctrine/mongodb-odm
    php ./composer.phar --no-update remove doctrine/mongodb-odm-bundle
    echo "removed doctrine MongoDB bundles"
fi;
