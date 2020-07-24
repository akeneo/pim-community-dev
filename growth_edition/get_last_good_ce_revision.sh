#/bin/bash

set -e

TMP_FILE=$(mktemp)

curl --silent --fail "https://circleci.com/api/v2/project/gh/akeneo/pim-community-dev/pipeline?circle-token=$CIRCLECI_API_TOKEN&branch=master" -H 'Accept: application/json' > $TMP_FILE

PIPELINE_ID=$(jq -rs '[.[0].items[] | select(.trigger.type == "schedule")][0] .id'  $TMP_FILE)
COMMIT_HASH=$(jq -rs '[.[0].items[] | select(.trigger.type == "schedule")][0] .vcs.revision' $TMP_FILE)

curl --silent --fail "https://circleci.com/api/v2/pipeline/$PIPELINE_ID/workflow?circle-token=$CIRCLECI_API_TOKEN" -H 'Accept: application/json' > $TMP_FILE

SUCCESSFUL_WORKFLOW_ID=$(cat $TMP_FILE | jq -rs '.[].items[] | select (.status == "success")| .id')

rm $TMP_FILE

if [ -z "$SUCCESSFUL_WORKFLOW_ID" ]; then
    echo "No successful revision found on the last execution" >&2
    exit 1;
fi

echo $COMMIT_HASH
