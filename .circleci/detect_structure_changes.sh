#!/usr/bin/env bash

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

echo "Export env vars from .env..."
export $(cat .env)

echo "Checkout 4.0 branch..."
git branch -D real40 || true
git checkout -b real40 --track origin/4.0
if [ -d "vendor/akeneo/pim-community-dev" ]; then
    pushd vendor/akeneo/pim-community-dev
    git branch -D real40 || true
    git checkout -b real40 --track origin/4.0
    popd
fi

echo "Copy CE migrations into EE if to install 4.0 branch..."
if [ -d "vendor/akeneo/pim-community-dev" ]; then
    cp -R vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema
fi

echo "Clean cache..."
APP_ENV=test make cache

echo "Install 4.0 database and indexes..."
APP_ENV=test make database

echo "Restore Git repository..."
if [ -d "vendor/akeneo/pim-community-dev" ]; then
    git checkout -- .
fi

echo "Checkout PR branch..."
git checkout $PR_BRANCH
if [ -d "vendor/akeneo/pim-community-dev" ]; then
    pushd vendor/akeneo/pim-community-dev
    (curl --output /dev/null --silent --head --fail https://github.com/akeneo/pim-community-dev/tree/${PR_BRANCH} && git checkout $PR_BRANCH) || git checkout master
    popd
fi

echo "Copy CE migrations into EE to launch branch migrations..."
if [ -d "vendor/akeneo/pim-community-dev" ]; then
    cp -R vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema
fi

echo "Clean cache..."
APP_ENV=test make cache

echo "Launch branch migrations..."
docker-compose run -u www-data php bin/console doctrine:migrations:migrate --env=test --no-interaction

echo "Dump 4.0 with migrations database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=$APP_DATABASE_PASSWORD --user=$APP_DATABASE_USER $APP_DATABASE_NAME | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > /tmp/dump_40_database_with_migrations.sql

echo "Dump 4.0 with migrations index..."
docker-compose exec -T elasticsearch curl -XGET "$APP_INDEX_HOSTS/_all/_mapping"|json_pp --json_opt=canonical,pretty > /tmp/dump_40_index_with_migrations.json

echo "Install branch database and indexes..."
APP_ENV=test make database

echo "Dump branch database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=$APP_DATABASE_PASSWORD --user=$APP_DATABASE_USER $APP_DATABASE_NAME | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' > /tmp/dump_branch_database.sql

echo "Dump branch index..."
docker-compose exec -T elasticsearch curl -XGET "$APP_INDEX_HOSTS/_all/_mapping"|json_pp --json_opt=canonical,pretty > /tmp/dump_branch_index.json

echo "Compare database 40+PR migrations from database PR..."
diff /tmp/dump_40_database_with_migrations.sql /tmp/dump_branch_database.sql --context=10

echo "Compare index 40+PR migrations from index PR..."
sed -i -r 's/[0-9]+_[0-9]+_[0-9]+_[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/version_uuid/g' /tmp/dump_40_index_with_migrations.json
sed -i -r 's/[0-9]+_[0-9]+_[0-9]+_[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/version_uuid/g' /tmp/dump_branch_index.json
diff /tmp/dump_40_index_with_migrations.json /tmp/dump_branch_index.json --context=10
