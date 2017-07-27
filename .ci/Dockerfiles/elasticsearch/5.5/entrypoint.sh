#!/bin/bash
set -e

trap 'exit 0' SIGTERM
bin/es-docker &
PID=$!
wait $PID
