#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console --quiet akeneo:batch:job csv_supplier_user_import -c "{\"filePath\": \"/srv/pim/vendor/akeneo/pim-onboarder/src/Bundle/Resources/fixtures/setup/supplier_users.csv\"}"
bin/console --quiet akeneo:batch:job csv_supplier_import -c "{\"filePath\": \"/srv/pim/vendor/akeneo/pim-onboarder/src/Bundle/Resources/fixtures/setup/suppliers.csv\"}"
