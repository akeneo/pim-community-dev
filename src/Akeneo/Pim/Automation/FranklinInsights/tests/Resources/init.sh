#!/usr/bin/env sh

set -exu

TARGET_HOST_NUMBER=${1?Arg 1 not defined, please precise the target host!}
TARGET_PORT=${2?Arg 2 not defined, please precise the target port!}

scp -P ${TARGET_PORT} -o "ForwardAgent yes" -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no dump.sql.gz akeneo@test-dev-feature-${TARGET_HOST_NUMBER}.core.akeneo.com:/tmp

ssh -A -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -p ${TARGET_PORT} akeneo@test-dev-feature-${TARGET_HOST_NUMBER}.core.akeneo.com << 'ENDSSH'
gunzip < /tmp/dump.sql.gz | mysql -u akeneo_pim -pakeneo_pim akeneo_pim
cd pim
bin/console --env=prod pim:product:index --all
sed -i s#https://ask-franklin.cloud.akeneo.com#https://ask-franklin-test.dev.cloud.akeneo.com# src/Akeneo/Pim/Automation/FranklinInsights/Infrastructure/Symfony/Resources/config/client/franklin.yml
rm -rf var/cache/*
ENDSSH
