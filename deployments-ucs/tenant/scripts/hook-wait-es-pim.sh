#!/bin/sh
set -e

MAX_COUNTER=120
COUNTER=1
SLEEP_TIME=5

log_json() {
    currentDate=$(date +"%G-%m-%d %T.%N")
    logWithMessage=$(printf '{"channel": "hook-wait-es-pim","context": {"message": "%s"},"datetime": {"date": "%s","timezone": "Etc/UTC","timezone_type": "3"}, "level_name": "%s"}' "$2" "${currentDate}" "$1")
    echo "${logWithMessage}" >&2
}

log_json "INFO" "Checking Elasticsearch connectivity..."
while ! curl --fail -s "http://elasticsearch-client:9200/_cluster/health?wait_for_status=yellow&timeout=1s"; do
    curl -s "http://elasticsearch-client:9200/_cluster/health" || true
    COUNTER=$((${COUNTER} + 1))
    if [ ${COUNTER} -gt ${MAX_COUNTER} ]; then
        TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
        log_json "WARNING" "We have been waiting for Elasticsearch for too long: ${TIME_WAITED} seconds; failing."
        exit 1
    fi;
    sleep ${SLEEP_TIME}
done
TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
log_json "INFO" "We have been waiting for Elasticsearch ${TIME_WAITED} seconds!"
