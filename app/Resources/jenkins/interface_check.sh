#!/bin/bash

OUTPUT=''
for pattern in Product Attribute Category ProductValue; do
    OUTPUT=`find $1 -type f -name "*.php" | grep -v "/Tests/" | xargs grep -P "[^a-zA-Z']$pattern([ ,;)]|\$)" | grep -v "*" | grep -v "namespace" | grep -v "class $pattern" | tr "\n" "#"`$OUTPUT
done
if [ -z "$OUTPUT" ]; then
    exit 0
else
    echo -n $OUTPUT | tr "#" "\n"
    exit 1
fi
