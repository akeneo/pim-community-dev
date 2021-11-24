#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console akeneo:onboarder:setup-database --no-table-creation -vvv --no-interaction
