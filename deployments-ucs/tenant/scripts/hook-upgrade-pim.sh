#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console doctrine:migrations:sync-metadata-storage --no-interaction --quiet
bin/console doctrine:migration:migrate -vvv --allow-no-migration --no-interaction
bin/console akeneo:elasticsearch:update-total-fields-limit -vvv --no-interaction
