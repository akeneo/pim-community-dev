#!/bin/bash
pushd `dirname ${0}` > /dev/null
cd /home/franck/code/pim-enterprise-dev/infra
helm lint ./pim
helm package -u ./pim/ --version 4.0.0-master  --app-version 4.0.0-master
helm gcs push ./pim-4.0.0-master.tgz akeneo-charts-dev --force 
popd > /dev/null
