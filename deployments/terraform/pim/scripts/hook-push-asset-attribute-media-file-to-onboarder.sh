#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console akeneo:synchronization:push-media-files-asset-attribute-to-middleware -vvv
