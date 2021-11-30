#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console --quiet pim:installer:db --catalog ${PIM_CATALOG}
