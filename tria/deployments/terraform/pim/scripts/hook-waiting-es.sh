#!/usr/bin/bash
set -e

MAX_COUNTER=120
COUNTER=1
SLEEP_TIME=5

echo "Checking Elasticsearch connectivity..."
while ! curl --fail "http://elasticsearch-client:9200/_cluster/health?wait_for_status=yellow&timeout=1s&pretty"; do
    curl "http://elasticsearch-client:9200/_cluster/health?pretty" || true
    COUNTER=$((${COUNTER} + 1))
    if [ ${COUNTER} -gt ${MAX_COUNTER} ]; then
        TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
        echo "We have been waiting for Elasticsearch for too long: $TIME_WAITED seconds; failing." >&2
        exit 1
    fi;
    sleep $SLEEP_TIME
done
TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
echo "We have been waiting for Elasticsearch $TIME_WAITED seconds!"
