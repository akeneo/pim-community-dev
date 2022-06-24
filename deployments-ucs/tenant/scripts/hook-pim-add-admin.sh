#!/usr/bin/bash
set -euo pipefail

cd /srv/pim
bin/console --quiet pim:user:create ${PIM_ADMIN_LOGIN} "${PIM_ADMIN_PASSWORD}" ${PIM_ADMIN_EMAIL} "${PIM_ADMIN_FIRSTNAME}" "${PIM_ADMIN_LASTNAME}" ${PIM_ADMIN_UI_LOCALE} --admin -n
