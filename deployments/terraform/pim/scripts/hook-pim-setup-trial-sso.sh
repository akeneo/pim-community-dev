#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console --quiet akeneo:free-trial:setup-sso
