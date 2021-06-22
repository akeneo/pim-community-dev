#!/usr/bin/bash
set -x
set -e

#Copy PIM entrypoint files into web-src volume
cp -p -r -f /srv/pim/public/* /srv/pim/public/.htaccess /web-src/.

#Copy Big Commerce back entrypoint files into connectors-web-src volume
mkdir -p /connectors-web-src/bigcommerce/back/public
cp -p -r -f /srv/pim/connectors/bigcommerce/back/public/* /connectors-web-src/bigcommerce/back/public/.

#Copy Big Commerce front entrypoint files into connectors-web-src volume
mkdir -p /connectors-web-src/bigcommerce/front
cp -p -r -f /srv/pim/connectors/bigcommerce/front/* /connectors-web-src/bigcommerce/front/.
