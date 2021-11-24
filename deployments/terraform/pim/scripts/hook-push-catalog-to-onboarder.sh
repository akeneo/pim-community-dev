#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console akeneo:synchronization:push-catalog-to-onboarder -vvv
