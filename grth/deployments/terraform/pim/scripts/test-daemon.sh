#!/usr/bin/bash
set -x
set -e
apt-get -qq update
apt-get -qq --no-install-recommends --no-install-suggests --yes  install curl jq ca-certificates

LOGIN_DATA=$(mktemp)
COOKIE_JAR=$(mktemp)

CURL="curl --connect-timeout 10 --retry 5 --retry-delay 5"

echo "Testing ${TARGET}"
timeout 500 curl --retry 300 --retry-delay 30 -k ${TARGET} >/dev/null
echo -n "_target_path=&_username=$LOGIN&_password=$PASSWORD&_csrf_token=" >$LOGIN_DATA
${CURL} --cookie-jar $COOKIE_JAR $TARGET/user/login | grep "_csrf_token" | cut -d '"' -f 6 >>$LOGIN_DATA
${CURL} -o /dev/null -XPOST --cookie $COOKIE_JAR --cookie-jar $COOKIE_JAR --data @$LOGIN_DATA $TARGET/user/login-check

${CURL} $TARGET/job-instance/rest/export \
    -H 'x-requested-with: XMLHttpRequest' \
    -H 'content-type: application/x-www-form-urlencoded; charset=UTF-8' \
    --cookie $COOKIE_JAR --cookie-jar $COOKIE_JAR \
    --data-raw '{"code":"test_job","label":"test_job","alias":"csv_locale_export","connector":"Akeneo CSV Connector"}'

JOBID=$(${CURL} -H 'x-requested-with: XMLHttpRequest' -XPOST --cookie $COOKIE_JAR --cookie-jar $COOKIE_JAR $TARGET/job-instance/rest/export/test_job/launch | cut -d '"' -f 4 | cut -d '/' -f 4)
RETRY=30
while [ "$(${CURL} "$TARGET/job-execution/rest/${JOBID}" --cookie $COOKIE_JAR --cookie-jar $COOKIE_JAR | jq '.status')" != '"Completed"' -a $RETRY -gt 0 ]; do
    let RETRY--
    echo "Wait..."
    sleep 1
done
if [ $RETRY -gt 0 ]; then
    exit 0
fi
exit 1
