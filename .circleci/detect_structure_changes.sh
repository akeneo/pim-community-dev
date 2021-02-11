#!/usr/bin/env bash

# Detect if migrations related to a structure change are missing.
#
# The same script is used for both CE and EE builds. In both cases, an EE is installed.
# For a CE build, EE master branch is used with CE $PR_BRANCH.
# For an EE build, EE $PR_BRANCH is used. If CE $PR_BRANCH exists, then it is used. Otherwise CE master is used.
#
# It works in 4 steps:
#   - step 1: Checkout 5.0 code to be able to install a 5.0 database and index.
#   - step 2: Checkout back to PR code to be able to apply PR migrations on the 5.0 database and index. Dump the results.
#   - step 3: Install fresh branch database and indexes. Dump the results.
#   - step 4: Compare the results of step 3 and step 4. If there is a diff, that means a migration is missing.

set -eu

usage() {
    echo "Usage: $0 BRANCH"
    echo
    echo "Example:"
    echo "    $0 TIP-1283"
    echo
    exit 1;
}

if [ $# -ne 1 ]; then
    usage
    exit -1
else
    PR_BRANCH=$1
fi

mkdir /tmp/structure_changes
mkdir -p ~/.composer
sudo chown 1000:1000 ~/.composer

## STEP 1: install 5.0 database and index
echo "##"
echo "## STEP 1: install 5.0 database and index"
echo "##"

echo "Save composer.lock"
cp composer.lock /tmp/composer.lock

echo "Checkout EE 5.0 branch..."
git branch -D real50 || true
git checkout -b real50 --track origin/5.0

echo "Creation of image with php 7.3..."
make php-image-dev

echo "Update composer dependencies"
make vendor

echo "Copy CE migrations into EE to install 5.0 branch..."
cp -R vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema

echo "Enable Onboarder bundle on 5.0 branch..."
sudo chown 1000:1000 composer.json
docker-compose run -u www-data --rm php php /usr/local/bin/composer config repositories.onboarder '{ "type": "vcs", "url": "https://github.com/akeneo/pim-onboarder.git", "branch": "master" }'
docker-compose run -u www-data --rm php php -d memory_limit=5G /usr/local/bin/composer require "akeneo/pim-onboarder:^4.2.1"
if [ -d "vendor/akeneo/pim-onboarder" ]; then
    sed -i "s~];~    Akeneo\\\Onboarder\\\Bundle\\\PimOnboarderBundle::class => ['all' => true],\n];~g" ./config/bundles.php
fi

echo "Export env vars from .env..."
export $(cat .env)

echo "Use the database akeneo_pim_test..."
echo "APP_DATABASE_NAME=akeneo_pim_test" >> .env.test.local
echo "APP_PRODUCT_AND_PRODUCT_MODEL_INDEX_NAME=akeneo_pim_product_and_product_model_test" >> .env.test.local
echo "APP_CONNECTION_ERROR_INDEX_NAME=akeneo_connectivity_connection_error_test" >> .env.test.local

echo "Clean cache..."
APP_ENV=test make cache

echo "Install 5.0 database and indexes..."
APP_ENV=test make database


## STEP 2: apply PR migrations on 5.0 database and index
echo "##"
echo "## STEP 2: apply PR migrations on 5.0 database and index"
echo "##"

echo "Restore Git repository as how it was at the beginning..."
git clean -f
git checkout -- .

echo "Checkout EE PR branch (or master if it does not exist)..."
git checkout $PR_BRANCH || git checkout master
cp /tmp/composer.lock ./composer.lock
touch composer.lock
make vendor

echo "Copy CE migrations into EE to launch branch migrations..."
cp -R vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema

echo "Enable Onboarder bundle on PR branch..."
if [ -d "vendor/akeneo/pim-onboarder" ]; then
    sed -i "s~];~    Akeneo\\\Onboarder\\\Bundle\\\PimOnboarderBundle::class => ['all' => true],\n];~g" ./config/bundles.php
fi

echo "Export env vars from .env..."
export $(cat .env)

echo "Use the database akeneo_pim_test..."
echo "APP_DATABASE_NAME=akeneo_pim_test" >> .env.test.local
echo "APP_PRODUCT_AND_PRODUCT_MODEL_INDEX_NAME=akeneo_pim_product_and_product_model_test" >> .env.test.local
echo "APP_CONNECTION_ERROR_INDEX_NAME=akeneo_connectivity_connection_error_test" >> .env.test.local

echo "Clean cache..."
APP_ENV=test make cache

echo "Launch branch migrations..."
docker-compose run -u www-data php bin/console doctrine:migrations:migrate --env=test --no-interaction

echo "Dump 5.0 with migrations database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=$APP_DATABASE_PASSWORD --user=$APP_DATABASE_USER $APP_DATABASE_NAME | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > /tmp/structure_changes/dump_50_database_with_migrations.sql

echo "Dump 5.0 with migrations index..."
docker-compose exec -T elasticsearch curl -XGET "$APP_INDEX_HOSTS/_all/_mapping"|json_pp --json_opt=canonical,pretty > /tmp/structure_changes/dump_50_index_with_migrations.json


## STEP 3: install fresh branch database and indexes
echo "##"
echo "## STEP 3: install fresh branch database and indexes"
echo "##"

echo "Install fresh branch database and indexes..."
APP_ENV=test make database

echo "Dump branch database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=$APP_DATABASE_PASSWORD --user=$APP_DATABASE_USER $APP_DATABASE_NAME | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > /tmp/structure_changes/dump_branch_database.sql

echo "Dump branch index..."
docker-compose exec -T elasticsearch curl -XGET "$APP_INDEX_HOSTS/_all/_mapping"|json_pp --json_opt=canonical,pretty > /tmp/structure_changes/dump_branch_index.json


## STEP 4: compare the results
echo "##"
echo "## STEP 4: compare the results"
echo "##"

echo "Compare database 50+PR migrations from database PR..."
diff /tmp/structure_changes/dump_50_database_with_migrations.sql /tmp/structure_changes/dump_branch_database.sql --context=10

echo "Compare index 50+PR migrations from index PR..."
sed -i -r 's/([0-9]+_[0-9]+_[0-9]+_)?[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/version_uuid/g' /tmp/structure_changes/dump_50_index_with_migrations.json
sed -i -r 's/[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/version_uuid/g' /tmp/structure_changes/dump_branch_index.json
diff /tmp/structure_changes/dump_50_index_with_migrations.json /tmp/structure_changes/dump_branch_index.json --context=10
