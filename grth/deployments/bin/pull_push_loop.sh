#!/usr/bin/env bash

usage() {
    echo "Usage: $0 GITHUB_DIR"
    exit 1
}

if [ $# -ne 1 ]; then
    usage
    exit 1
fi

RETRY_NUM=3
RETRY_EVERY=10

GITHUB_DIR=$1

NUM=${RETRY_NUM}

cd ${GITHUB_DIR}
until (git pull --no-edit && git push)
do
  1>&2 echo "failure ... retrying ${NUM} more times"
  sleep ${RETRY_EVERY}
  ((NUM--))

  if [ ${NUM} -eq 0 ]
  then
    1>&2 echo "git pull --no-edit && git push was not successful after ${RETRY_NUM} tries"
    exit 1
  fi
done

echo success!
