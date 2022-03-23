#!/bin/bash

# Raccoons squad internal script to tackle tech debt.
# If you have any questions: feel free to ask at #squad-raccoons
# This script searches database coupling of the channel domain with other Raccoons domains.

# Extra mile:
# vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/CatalogVolumeMonitoringBundle \

grep -rnil \
  -e "pim_catalog_channel" -e "pim_catalog_locale" \
  --exclude "src/Akeneo/AssetManager/back/Infrastructure/Symfony/Command/MigrationPAM/ExportAssetsIntoCSVFiles/FindVariations.php" \
  components/tailored-import \
  components/tailored-export \
  src/Akeneo/AssetManager \
  src/Akeneo/ReferenceEntity \
  vendor/akeneo/pim-community-dev/src/Akeneo/Tool/Bundle/MeasureBundle \
  vendor/akeneo/pim-community-dev/src/Akeneo/Tool/Component/Batch \
  vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/ImportExportBundle \
  vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Job \
  vendor/akeneo/pim-community-dev/src/Akeneo/Tool/Bundle/BatchBundle
