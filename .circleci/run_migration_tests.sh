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

echo "Checkout master branch..."
git branch -D realmaster || true
git checkout -b realmaster --track origin/master

echo "Clean cache..."
make cache

echo "Install master database and index..."
make database

echo "Checkout PR branch..."
git checkout $PR_BRANCH

echo "Clean cache..."
make cache

echo "Launch branch migrations..."
docker-compose run -u www-data php bin/console doctrine:migrations:migrate --env=test --no-interaction

echo "Dump master with migrations database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=$APP_DATABASE_PASSWORD --user=$APP_DATABASE_USER $APP_DATABASE_NAME > /tmp/dump_master_database_with_migrations.sql

echo "Dump master with migrations index..."
curl -XGET 'localhost:9210/_all/_mapping'|json_pp --json_opt=canonical,pretty > /tmp/dump_master_index_with_migrations.json

echo "Install branch database and index..."
make database

echo "Dump branch database..."
docker-compose exec -T mysql mysqldump --no-data --skip-opt --skip-comments --password=$APP_DATABASE_PASSWORD --user=$APP_DATABASE_USER $APP_DATABASE_NAME > /tmp/dump_master_database.sql

echo "Dump branch index..."
curl -XGET 'localhost:9210/_all/_mapping'|json_pp --json_opt=canonical,pretty > /tmp/dump_master_index.json

echo "Compare database master+PR migrations from database PR..."
diff /tmp/dump_master_database_with_migrations.sql /tmp/dump_master_database.sql

echo "Compare index master+PR migrations from index PR..."
sed -i -r 's/[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/uuid/g' /tmp/dump_master_index_with_migrations.json
sed -i -r 's/[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}/uuid/g' /tmp/dump_master_index.json
diff /tmp/dump_master_index_with_migrations.json /tmp/dump_master_index.json
