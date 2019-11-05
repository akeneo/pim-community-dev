#!/bin/sh

TESTSUITE=$1

TESTFILES=$(docker-compose run --rm -T php vendor/bin/behat --list-scenarios -p legacy -s $TESTSUITE | circleci tests split --split-by=timings)

fail=0
counter=1
total=$(($(echo $TESTFILES | grep -o ' ' | wc -l) + 1))

for TESTFILE in $TESTFILES; do
    echo "$TESTFILE ($counter/$total):"
    output=$(basename $TESTFILE)_$(uuidgen)

    docker-compose exec -u www-data -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TESTSUITE $TESTFILE ||
    docker-compose exec -u www-data -T fpm ./vendor/bin/behat --format pim --out var/tests/behat/${output} --format pretty --out std --colors -p legacy -s $TESTSUITE $TESTFILE
    fail=$(($fail + $?))
    counter=$(($counter + 1))
done

return $fail
