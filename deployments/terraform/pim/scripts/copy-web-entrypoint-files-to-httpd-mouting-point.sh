#!/usr/bin/bash
set -e

#Copy PIM entrypoint files into web-src volume
cp -p -r -f /srv/pim/public/* /srv/pim/public/.htaccess /web-src/.

#Supplier portal front build
if [[ -d '/srv/pim/components/supplier-portal-supplier/front/build' ]];then
  mkdir -p /web-src/supplier-portal/supplier/
  cp -p -r -f /srv/pim/components/supplier-portal-supplier/front/build/* /web-src/supplier-portal/supplier/
fi
