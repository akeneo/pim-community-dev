#!/bin/sh

SUITE=$1
shift
TESTFILES=$@

echo $TESTFILES

fail=0
counter=1
total=$(($(echo $TESTFILES | grep -o ' ' | wc -l) + 1))

for TESTFILE in $TESTFILES; do
    echo "docker-compose exec -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${SUITE}/${output} --format pretty --out std --colors -p legacy -s $SUITE $TESTFILE"
    echo "$TESTFILE ($counter/$total):"
    output=$(basename $TESTFILE)_$(uuidgen)
    docker-compose exec -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${SUITE}/${output} --format pretty --out std --colors -p legacy -s $SUITE $TESTFILE || \
    docker-compose exec -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${SUITE}/${output} --format pretty --out std --colors -p legacy -s $SUITE $TESTFILE
    fail=$(($fail + $?))
    counter=$(($counter + 1))
done

return $fail
