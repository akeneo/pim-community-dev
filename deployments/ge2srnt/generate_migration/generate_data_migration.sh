#!/bin/bash
set -euox pipefail


# This script generates the migration scripts that are necessary to migrate an instance from Growth Edition to production.
# Basically, here are the steps"
# - generate a script to create missing tables between GE and EE
# - generate a script to create missing jobs between GE and EE
# - hardcoded SQL requests to create missing rights
# - upload everything to bucket

function export_schema() {
    local -r file=$1
    make down
    make dependencies
    #pim-behat fails in GE
    make pim-test

    # tail removes warning about using file for password connection
    docker-compose exec mysql mysqldump -u root -proot akeneo_pim_test | tail -n +2 >"$file"
}

SCRIPT_PATH=$(dirname $(realpath -s $0))


echo "Shutdown all containers"
cd $SCRIPT_PATH/../../../
make down
cd $SCRIPT_PATH/../../
make down

echo "Create database in GE and EE"
cd $SCRIPT_PATH/../../../
export_schema "/tmp/ee_schema.sql"
make down
cd $SCRIPT_PATH/../../
export_schema "/tmp/ge_schema.sql"

echo "Generate database diff"
docker-compose exec mysql mysql -u root -proot -e "drop database if exists akeneo_pim_ee; drop database if exists akeneo_pim_ge;"
docker-compose exec mysql mysql -u root -proot -e "create database akeneo_pim_ee; create database akeneo_pim_ge;"
docker-compose exec -T mysql mysql -u root -proot akeneo_pim_ee </tmp/ee_schema.sql
docker-compose exec -T mysql mysql -u root -proot akeneo_pim_ge </tmp/ge_schema.sql

tables=$(docker-compose exec -T mysql mysql -u root -proot < $SCRIPT_PATH/existing_table_in_ge_and_ee.sql)
tables_to_exclude=$(printf " --ignore-table akeneo_pim_ee.%s\n" $tables)
docker-compose exec -T mysql mysqldump $tables_to_exclude -u root -proot akeneo_pim_ee --skip-add-drop-table --no-data | tail -n +2 > /tmp/dump_schema_ge_to_ee.sql

echo "Generate job instance diff"

docker-compose exec -T mysql mysql -u root -proot < $SCRIPT_PATH/create_table_missing_job_instances.sql
docker-compose exec -T mysql mysqldump $tables_to_exclude -u root -proot akeneo_pim_ee --skip-add-drop-table ee_job_instance | tail -n +2 > /tmp/dump_data_job_instance_ee.sql

echo '
  insert into akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type) select code, label, job_name, status, connector, raw_parameters, type from ee_job_instance;
  drop table ee_job_instance;
' >> /tmp/dump_data_job_instance_ee.sql

echo "Upload migration scripts to a dedicated bucket"
gsutil cp /tmp/dump_schema_ge_to_ee.sql gs://mig-ge-to-srnt
gsutil cp /tmp/dump_data_job_instance_ee.sql gs://mig-ge-to-srnt
gsutil cp $SCRIPT_PATH/add_missing_permissions.sql gs://mig-ge-to-srnt
