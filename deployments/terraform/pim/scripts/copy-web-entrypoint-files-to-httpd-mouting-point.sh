#!/usr/bin/bash
set -e

#Copy PIM entrypoint files into web-src volume
cp -p -r -f /srv/pim/public/* /srv/pim/public/.htaccess /web-src/.

#Onboarder serenity front build
mkdir -p /web-src/onboarder/supplier/
cp -p -r -f /srv/pim/components/onboarder-supplier/front/build/* /web-src/onboarder/supplier/
