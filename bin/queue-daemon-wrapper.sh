#!/bin/bash
# This is a bash wrapper to handle SIGTERM signal sent from Kubernetes during a rolling-update.
# It enables grace restart with keeping running job until 1 day if necessary
set -e

log_as_monolog() {
    currentDate=$(date +"%G-%m-%d %T.%N")
    logWithMessage=$(printf '{"channel": "queue-daemon-wrapper","context": {"message": "%s"},"datetime": {"date": "%s","timezone": "Etc/UTC","timezone_type": "3"},"level": "250","level_name": "NOTICE"}' "$1" "${currentDate}")
    echo "${logWithMessage}" >&2
}

catch() {
    log_as_monolog 'Waiting with grace for wrapped process to finish.'
    wait $pid
    log_as_monolog 'Wrapped process finished. Ended with grace.'
}

trap 'catch' SIGTERM

log_as_monolog "Start loop $@"

while true; do
    bin/console $@ &
    pid=$!
    wait $pid
done
