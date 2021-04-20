#!/bin/bash -e

TARGET_DIR=$1

if [ -z "$TARGET_DIR" ] || [ ! -d $TARGET_DIR ]; then
    echo "The provided directory is not accessible: $TARGET_DIR" >&2
    exit 1
fi

EE_DIR=$(dirname $0)/..

cp $EE_DIR/Dockerfile $TARGET_DIR/
cp -r $EE_DIR/docker/*.conf $EE_DIR/docker/*.ini $TARGET_DIR/docker/
cp -r $EE_DIR/deployments $TARGET_DIR/

cp $EE_DIR/growth_edition/oneup_flysystem.yml $TARGET_DIR/config/packages/prod/
cp $EE_DIR/config/services/prod/storage.yml $TARGET_DIR/config/services/prod/

cp $EE_DIR/Makefile $TARGET_DIR
cp -r $EE_DIR/make-file $TARGET_DIR
cp $EE_DIR/config/fake_credentials_gcp.json $TARGET_DIR/config

# Install Monitoring Bundle
cp -r $EE_DIR/src/Akeneo/Platform/Bundle/MonitoringBundle $TARGET_DIR/src/Akeneo/Platform/Bundle/
cat $EE_DIR/growth_edition/routes.yml >> $TARGET_DIR/config/routes/routes.yml
sed -i '$ d' $TARGET_DIR/config/bundles.php
echo " Akeneo\Platform\Bundle\MonitoringBundle\AkeneoMonitoringBundle::class => ['all' => true]," >> $TARGET_DIR/config/bundles.php
echo "];"  >> $TARGET_DIR/config/bundles.php
echo "    monitoring_authentication_token: '%env(MONITORING_AUTHENTICATION_TOKEN)%'" >> $TARGET_DIR/config/services/pim_parameters.yml

# Set the version and its label
sed -i -e "s/ VERSION = 'master'/ VERSION = '$GROWTH_RELEASE_NAME'/" $TARGET_DIR/src/Akeneo/Platform/CommunityVersion.php
sed -i -e "s/ VERSION_CODENAME = 'Community master'/ VERSION_CODENAME = 'Growth Edition'/" $TARGET_DIR/src/Akeneo/Platform/CommunityVersion.php
sed -i -e "s/ EDITION = 'CE'/ EDITION = 'GE'/" $TARGET_DIR/src/Akeneo/Platform/CommunityVersion.php

# Remove specific EE crons
yq d --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'pim.jobs.rule-run'
yq d --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'pim.jobs.project-recalculate'
yq d --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'pim.jobs.project-notify-before-due-date'
yq d --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'pim.jobs.reference-entity-refresh-records'
yq d --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'pim.jobs.sso-log-rotate'
yq d --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'pim.jobs.asset-manager-update-mapping'
yq w --inplace $TARGET_DIR/deployments/terraform/pim/values.yaml 'global.extraLabels.type' grth
