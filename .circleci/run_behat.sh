#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$@

fail=0
counter=1
total=$(($(echo $TESTFILES | grep -o ' ' | wc -l) + 1))

for TESTFILE in $TESTFILES; do
    echo "$TESTFILE ($counter/$total):"
    uuid=$(uuidgen)
    docker-compose exec -T akeneo-behat ./bin/behat --format=pretty,junit --out=,var/tests/behat/behat_${uuid} $TESTFILE || \
    docker-compose exec -T akeneo-behat ./bin/behat --format=pretty,junit --out=,var/tests/behat/behat_${uuid} $TESTFILE
    fail=$(($fail + $?))
    counter=$(($counter + 1))
done

return $fail
