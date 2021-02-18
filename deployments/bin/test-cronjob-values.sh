#!bin/bash

BINDIR=$(dirname $(readlink -f $0))


docker run -u www-data eu.gcr.io/akeneo-ci/pim-enterprise-dev:${IMAGE_TAG} bin/console list > available_command.txt
yq read ${BINDIR}/../terraform/pim/values.yaml 'pim.jobs.*.pimCommand' > jobs_used.txt

while read line; do
command=$(echo ${line} | cut -f1 -d " ")
if  grep -q "${command}" available_command.txt ; then
    echo Command ${command} is available 🥳
else
    echo Command ${command} is not available 👎
    exit 1
fi
done <jobs_used.txt
