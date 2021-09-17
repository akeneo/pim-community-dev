# Code style

### Backend

We agreed to progressively adapt the code style to comply with php-cs-fixer's [@Symfony ruleset](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3.0/doc/ruleSets/Symfony.rst)
in every Akeneo product. However, in the PIM, the previous rules were much less restrictive (PSR-2 ruleset and a few additional rules).

In order to avoid a massive PR which would fix all PHP files to the new format, and inevitably cause a lot of conflicts on the ongoing PRs,
the decision was made, as a first step, to:
- keep the former rules when running the CI (the new rules are compatible with the former ones)
- provide developers with a git commit hook which will fix the newly updated / created PHP files according to the new ruleset. This way the code can be
progressively migrated without causing conflicts

In order to install this hook, one will need to:

- create a `.git/hooks/pre-commit` file with the following content:

```bash
#!/usr/bin/env bash
  
FILES=`git status --porcelain | grep -P '^[AM] .+\.php$' | cut -c 4- | tr '\n' ' '`

if [[ -z "$FILES" ]]
then
    exit 0
fi

PIM_CONTEXT=dev make lint-fix-back O=${FILES}
res=$?

if [[ $res -ne 0 ]]
then
    exit $res
fi

git add $FILES
exit 0
```

- make it executable (`chmod +x .git/hooks/pre-commit`)
- voilà!

This hook will fix added/updated files, and render a diff of the fixes to let the developer know what changes were actually made.
It is compatible with both `pim-community-dev` and `pim-enterprise-dev` repos.

At last, it can be easily disabled:
- by passing the `-n` (aka `--no-verify`) option to the `git commit` command
- or more permanently, by removing the pre-commit file or revoking execution permissions from it
