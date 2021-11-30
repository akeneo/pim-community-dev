#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console doctrine:migration:migrate -vvv --allow-no-migration --no-interaction
