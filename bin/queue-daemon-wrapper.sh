#!/bin/bash
# This is a bash wrapper to handle SIGTERM signal sent from Kubernetes during a rolling-update.
# It enables grace restart with keeping running job until 1 day if necessary
set -e

log_as_monolog() {
    export currentDate=$(date +"%G-%m-%d %T.%N")
    export msg="$1"
    php bin/json_handler.php >&2
}

catch() {
    log_as_monolog "Waiting with grace for wrapped process $pid to finish."
    kill -TERM $pid
    wait $pid
    log_as_monolog 'Wrapped process finished. Ended with grace.'
    exit 0
}

start_job() {
  bin/console $@ 2>&1 | while read -r line
  do
    log_as_monolog "$line"
  done
}

trap 'catch' SIGTERM

log_as_monolog "Start loop $@"

while true; do
    args=$@
    start_job $args &
    pid=$!
    wait $pid
done
