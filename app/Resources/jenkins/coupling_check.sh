#!/bin/bash

# Check the forbidden use statement to ensure appropriate de-coupling
OUTPUT=''

# CatalogBundle
for pattern in Oro EnrichBundle TransformBundle; do
    OUTPUT=`grep -r 'use.*'$pattern $1Pim/Bundle/CatalogBundle/ | tr "\n" "#"`$OUTPUT
done

# TransformBundle
for pattern in Oro EnrichBundle ImportExportBundle BaseConnector; do
    OUTPUT=`grep -r 'use.*'$pattern $1Pim/Bundle/TransformBundle/ | tr "\n" "#"`$OUTPUT
done

# BaseConnectorBundle
for pattern in Oro EnrichBundle ImportExportBundle ; do
    OUTPUT=`grep -r 'use.*'$pattern $1Pim/Bundle/BaseConnectorBundle/ | tr "\n" "#"`$OUTPUT
done

if [ -z "$OUTPUT" ]; then
    exit 0
else
    echo -n $OUTPUT | tr "#" "\n"
    exit 1
fi
