#!/bin/sh

# We need the TESTFILES var in $1
TESTFILES=$@

fail=0
counter=1
total=$(($(echo $TESTFILES | grep -o ' ' | wc -l) + 1))

for TESTFILE in $TESTFILES; do
    echo "$TESTFILE ($counter/$total):"
    output=$(basename $TESTFILE)_$(uuidgen)
    docker-compose exec -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy $TESTFILE || \
    docker-compose exec -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy $TESTFILE
    fail=$(($fail + $?))
    counter=$(($counter + 1))
done

return $fail
