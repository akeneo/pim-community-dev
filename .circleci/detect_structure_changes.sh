#!/usr/bin/env bash

# Detect if migrations related to a structure change are missing.
#
# The same script is used for both CE and EE builds. In both cases, an EE is installed.
# For a CE build, EE 7.0 branch is used with CE $PR_BRANCH.
# For an EE build, EE $PR_BRANCH is used. If CE $PR_BRANCH exists, then it is used. Otherwise CE 7.0 is used.
#
# It works in 4 steps:
#   - step 1: Checkout 6.0 code to be able to install a 6.0 database and index.
#   - step 2: Checkout back to PR code to be able to apply PR migrations on the 6.0 database and index. Dump the results.
#   - step 3: Install fresh branch database and indexes. Dump the results.
#   - step 4: Compare the results of step 3 and step 4. If there is a diff, that means a migration is missing.

set -eux

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

## STEP 1: install 6.0 database and index
echo "##"
echo "## STEP 1: install 6.0 database and index"
echo "##"

echo "Checkout EE 6.0 branch..."
git branch -D real60 || true
git checkout -b real60 --track origin/6.0
sudo chown 1000:1000 -R .

echo "Creation of image with php 8.0..."
make php-image-dev

docker-compose -f ./docker-compose.yml run -u www-data --rm php composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer false

echo "Update composer dependencies"
make vendor

if [ -d "vendor/akeneo/pim-community-dev" ]; then
    echo "Copy CE migrations into EE to install 6.0 branch..."
    cp -R vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema
fi

echo "Export env vars from .env..."
source .env
source .env.test

echo "Clean cache..."
APP_ENV=test make cache

echo "Install 6.0 database and indexes..."
APP_ENV=test make database


## STEP 2: apply PR migrations on 6.0 database and index
echo "##"
echo "## STEP 2: apply PR migrations on 6.0 database and index"
echo "##"

echo "Restore Git repository as how it was at the beginning..."
sudo chown circleci:circleci -R ../project
git clean -f
git checkout -- .

echo "Checkout EE PR branch (or 7.0 if it does not exist)..."
git checkout $PR_BRANCH || git checkout 7.0
curl --output /dev/null --silent --head --fail https://github.com/akeneo/pim-community-dev/tree/${PR_BRANCH} && \
    sed -i "s#akeneo/pim-community-dev\": \"7.0.x-dev@dev#akeneo/pim-community-dev\": \"dev-${PR_BRANCH}#" composer.json || \
    echo "No CE branch $PR_BRANCH found. I don't touch the EE dependencies."

sudo chown 1000:1000 -R .
make vendor

if [ -d "vendor/akeneo/pim-community-dev" ]; then
    echo "Copy CE migrations into EE to launch branch migrations..."
    cp -R vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema
fi

echo "Export env vars from .env..."
source .env
source .env.test

echo "Clean cache..."
APP_ENV=test make cache

echo "Launch branch migrations..."
docker-compose run --rm php bin/console doctrine:migrations:migrate --env=test --no-interaction

echo "Dump 6.0 with migrations database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=root --user=root $APP_DATABASE_NAME | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > /tmp/structure_changes/dump_60_database_with_migrations.sql

echo "Dump 6.0 with migrations index..."
docker-compose exec -T elasticsearch curl -XGET "$APP_INDEX_HOSTS/_all/_mapping"|json_pp --json_opt=canonical,pretty > /tmp/structure_changes/dump_60_index_with_migrations.json


## STEP 3: install fresh branch database and indexes
echo "##"
echo "## STEP 3: install fresh branch database and indexes"
echo "##"

echo "Install fresh branch database and indexes..."
APP_ENV=test make database

echo "Dump branch database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=root --user=root $APP_DATABASE_NAME | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > /tmp/structure_changes/dump_branch_database.sql

echo "Dump branch index..."
docker-compose exec -T elasticsearch curl -XGET "$APP_INDEX_HOSTS/_all/_mapping"|json_pp --json_opt=canonical,pretty > /tmp/structure_changes/dump_branch_index.json


## STEP 4: compare the results
echo "##"
echo "## STEP 4: compare the results"
echo "##"

echo "Compare database 60+PR migrations from database PR..."
diff /tmp/structure_changes/dump_60_database_with_migrations.sql /tmp/structure_changes/dump_branch_database.sql --context=10

echo "Compare index 60+PR migrations from index PR..."
sed -i -r 's/([0-9]+_[0-9]+_[0-9]+_)?[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/version_uuid/g' /tmp/structure_changes/dump_60_index_with_migrations.json
sed -i -r 's/[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/version_uuid/g' /tmp/structure_changes/dump_branch_index.json
diff /tmp/structure_changes/dump_60_index_with_migrations.json /tmp/structure_changes/dump_branch_index.json --context=10
