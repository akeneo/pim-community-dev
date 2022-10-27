#!/usr/bin/bash
set -e

MAX_COUNTER=120
COUNTER=1
SLEEP_TIME=5

log_json() {
    currentDate=$(date +"%G-%m-%d %T.%N")
    logWithMessage=$(printf '{"channel": "wait-dns","context": {"message": "%s"},"datetime": {"date": "%s","timezone": "Etc/UTC","timezone_type": "3"}, "level_name": "%s"}' "$2" "${currentDate}" "$1")
    echo "${logWithMessage}" >&2
}

apt-get update
apt-get install dnsutils -y

log_json "INFO" "Checking DNS connectivity..."
while ! host ${PFID}; do
    COUNTER=$((${COUNTER} + 1))
    if [ ${COUNTER} -gt ${MAX_COUNTER} ]; then
        TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
        log_json "WARNING" "We have been waiting for DNS for too long: ${TIME_WAITED} seconds; failing."
        exit 1
    fi;
    sleep ${SLEEP_TIME}
done
TIME_WAITED=$(( COUNTER*SLEEP_TIME ))
log_json "INFO" "We have been waiting for DNS ${TIME_WAITED} seconds!"
