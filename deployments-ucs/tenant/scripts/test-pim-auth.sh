#!/bin/sh
set -ex

export LOGIN_DATA=$(mktemp)
export COOKIE_JAR=$(mktemp)

CURL="curl -k --connect-timeout 10 --retry 5 --retry-delay 5 --retry-connrefused --http1.1"

echo "Testing ${TARGET}"
timeout 500 curl --retry 300 --retry-delay 30 -k ${TARGET} --http1.1 > /dev/null
echo -n "_target_path=&_username=${LOGIN}&_password=${PASSWORD}&_csrf_token=" > ${LOGIN_DATA}
cat ${LOGIN_DATA}

${CURL} --cookie-jar ${COOKIE_JAR} ${TARGET}/user/login | grep "_csrf_token" | cut -d '"' -f 6 >> ${LOGIN_DATA}
${CURL} -XPOST --cookie ${COOKIE_JAR} --cookie-jar ${COOKIE_JAR} --data @${LOGIN_DATA} ${TARGET}/user/login-check
${CURL} -L -m 5 --cookie ${COOKIE_JAR} ${TARGET}/dashboard
${CURL} -L -m 5 -w "%{http_code}" --cookie ${COOKIE_JAR} ${TARGET}/dashboard | grep 200

exit 0
