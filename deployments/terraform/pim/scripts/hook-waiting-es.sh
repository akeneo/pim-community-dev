#!/usr/bin/bash
set -e

MAX_COUNTER=120
COUNTER=1
SLEEP_TIME=5

echo "We will check the connectivity with Elasticsearch"
while ! curl -s -k --fail "elasticsearch-client:9200/_cluster/health?wait_for_status=green&timeout=1s" > /dev/null; do
    COUNTER=$((${COUNTER} + 1))
    if [ ${COUNTER} -gt ${MAX_COUNTER} ]; then
        echo "We have been waiting for Elasticsearch too long already; failing." >&2
        exit 1
    fi;
    sleep $SLEEP_TIME
done
TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
echo "We have been waiting for Elasticsearch $TIME_WAITED seconds!"
