#!/usr/bin/env bash

set -eu

git remote add pim-enterprise-dev git@github.com:akeneo/pim-enterprise-dev.git || true
git fetch --all

git cherry-pick --abort || true
git reset --hard origin/master
git clean -fd

LAST_TAG=$(git show origin/master:last_backport.txt || echo "monorepo")
DIRECTORIES=(
  frontend/build
  frontend/webpack
  components/identifier-generator
  src/Acme
  src/Akeneo
  src/Behat
  src/Oro
  front-packages/akeneo-design-system
  front-packages/shared
  upgrades/schema
  upgrades/test_schema
  tests
)
COMMITS=$(git log --reverse --first-parent --pretty=format:"%h" ${LAST_TAG}..pim-enterprise-dev/master ${DIRECTORIES[@]})

for commit in ${COMMITS[@]}; do
  message="$(git log --format=%s -n 1 ${commit})"
  author="$(git log --format='%an <%ae>' -n 1 ${commit})"
  date="$(git log --format='%ad' -n 1 ${commit})"

  echo ${commit} ${message} ${date}

  git cherry-pick --allow-empty -Xtheirs -Xfind-renames --no-commit -m 1 ${commit} || true
  git reset HEAD

  if [ -d "tests/community" ]
  then
    rsync -av tests/community/ tests/
    rm -rf tests/enterprise/ tests/community/
  fi

  find tests/ -type f -exec perl -pi -e "s#tests/community#tests#g" {} +
  sed -i 's#(string) new Path('"'"'tests'"'"', '"'"'community'"'"'#(string) new Path('"'"'tests'"'"'#' tests/back/Integration/IntegrationTestsBundle/Configuration/Catalog.php

  git add ${DIRECTORIES[@]}
  git checkout .
  git clean -fd
  git commit --allow-empty --message="${message}" --author="${author}" --date="${date}"
done

git log --first-parent pim-enterprise-dev/master --pretty=format:"%h" -n 1 > last_backport.txt
git add last_backport.txt
git commit -m "Updates last commit backported"
