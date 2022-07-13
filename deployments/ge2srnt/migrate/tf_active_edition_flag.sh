#!/bin/bash
set -euo pipefail

# Summary:
# - Active Edition Flag for brand new instance
# Part of script splitted from recreate_instance_srnt_infra.sh for idempotence, these action has to be performed at every pipeline execution.

## Print original main.tf.json
echo "--- Original main.tf.json ---"
cat main.tf.json.ori
echo "---------------------------------------------------"

## Add use_edition_flag=true dans main.tf.json
echo "INFO: Adding use_edition_flag in main.tf.json"
yq w -jPi main.tf.json 'module.pim.use_edition_flag' true
# Managing pim-monitoring module depreciation
if [[ $(jq -r '.module  | has("pim-monitoring") ' main.tf.json) == "true" ]]; then
    yq w -jPi main.tf.json 'module.pim-monitoring.use_edition_flag' '${module.pim.use_edition_flag}'
else
    echo "INFO: This instance doesn't have pim-monitoring terraform module"
fi

## Remove disk and snapshot from main.tf
echo "INFO: Delete mysql disk informations from main.tf.json"
yq d -jPi main.tf.json 'module.pim.mysql_disk_name'
yq d -jPi main.tf.json 'module.pim.mysql_disk_description'
yq d -jPi main.tf.json 'module.pim.mysql_source_snapshot'

## Print modified main.tf.json
echo "--- New main.tf.json ---"
cat main.tf.json
echo "---------------------------------------------------"
## Print diff between Original and New  main.tf.json
echo "--- Difference between original and modified  main.tf.json"
diff main.tf.json.ori main.tf.json || true
echo "---------------------------------------------------"

echo "--- Set minimal catalog and enable catalog installation helm hook ---"
yq w -i values.yaml pim.defaultCatalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal
yq w -i values.yaml pim.hook.installPim.enabled true
