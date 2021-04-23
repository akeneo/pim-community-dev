# Running the tests in Akeneo PIM

## Introduction

The source code of Akeneo PIM is extensively tested, with static analysis and automated tests, both on the back-end and the front-end.
This document presents a list of all the tools that are used to test Akeneo PIM, what kind of testing they are used for, and how to run them.

All those tools are currently run on the Akeneo CI. For each tool, examples are provided explaining how to run all the tests at once, only one, or a specific set.
Also, the examples present how to run the tests with a local installation, or with Docker and Docker Compose (with the `docker-compose.yml` file provided in the PIM).

Only their usage for Akeneo PIM is described. If you want more information, please refer to the official websites.

## Back-end

### PHP Coupling Detector

Official website: https://github.com/akeneo/php-coupling-detector

This tool, maintained by Akeneo, detects the coupling issues in the code base, according to the coupling rules defined in the project.
By default, the rules are defined in a `.php_cd` file, placed at the root of the project.

In Akeneo PIM, there is one configuration file [at the root of the project](https://github.com/akeneo/pim-community-dev/blob/master/.php_cd.php),
per bounded context (read the [architecture documentation](https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/ARCHITECTURE.md) to learn more about bounded contexts).

If you want to run all the rules of a bounded context (here, the UserManagement one):
```bash
$ vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement
```

If you want to use `docker-compose`:
```bash
$ docker-compose run php vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement
```

If you want to only run the rules on a specific folder:
```bash
$ vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement/Bundle/Controller
```

If you want to only run the rules on a specific file:
```bash
$ vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement/Bundle/Controller/UserController.php
```

### PHP Coding Standards Fixer (php-cs-fixer)

Official website: https://github.com/FriendsOfPHP/PHP-CS-Fixer

This tool can both detect and fix issues in your code, according to the standard you have defined. Akeneo PIM follows the PSR-2 standard, plus a few more rules.
This configuration is defined in the file [.php_cs.php](https://github.com/akeneo/pim-community-dev/blob/master/.php_cs.php).

If you want to detect all the violations at once:
```bash
$ vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php
```

If you want to use `docker-compose`:
```bash
$ docker-compose run php vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php
```

If you want to only detect the violations for a specific folder:
```bash
$ vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/UserManagement/Bundle/Controller
```

If you want to only detect the violations for a specific file:
```bash
$ vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/UserManagement/Bundle/Controller/UserController.php
```

If you want to **fix** all the violations, remove the `--dry-run` option:
```bash
$ vendor/bin/php-cs-fixer fix --diff --config=.php_cs.php
```

### phpspec

Official website: https://www.phpspec.net/en/stable/

`phpspec` is used to unit test Akeneo PIM. Test suites are defined in the file [phpspec.yml.dist](https://github.com/akeneo/pim-community-dev/blob/master/phpspec.yml.dist).
You can copy this file as `phpspec.yml` to edit it, if you want to use additional extensions that require configuration, for instance.
`phpspec.yml` is present in `.gitignore`, so it will not be committed.

If you want to run all the tests at once:
```bash
$ vendor/bin/phpspec run
```

If you want to use `docker-compose`:
```bash
$ docker-compose run php vendor/bin/phpspec run
```

If you want to run the tests from a specific folder:
```bash
$ vendor/bin/phpspec run src/Akeneo/UserManagement/Component/spec/Model
```

If you want to run the tests from a specific file:
```bash
$ vendor/bin/phpspec run src/Akeneo/UserManagement/Component/spec/Model/GroupSpec.php
```

If you want to run a specific test, add the line where the test starts:
```bash
$ vendor/bin/phpspec run src/Akeneo/UserManagement/Component/spec/Model/GroupSpec.php:42
```

### PHPUnit

Official website: https://phpunit.de/

In Akeneo PIM, `PHPUnit` is used for integration tests, and a few remaining legacy unit tests that were not migrated to `phpspec`.
Test suites are defined in the file [app/phpunit.xml.dist](https://github.com/akeneo/pim-community-dev/blob/master/app/phpunit.xml.dist)
(one for unit tests, one for integration tests), with the rest of `PHPUnit` configuration.

If you want to run all the tests at once (not recommended, as it is way to long; that's why the Akeneo CI uses a queue and multiple machines in parallel to run them):
```bash
$ phpunit -c phpunit.xml.dist
```

If you want to use `docker-compose`:
```bash
$ docker-compose run php phpunit -c phpunit.xml.dist
```

If you want to run a specific test suite (in this example, the legacy unit tests suite):
```bash
$ phpunit -c phpunit.xml.dist --testsuite="PIM_Unit_Test"
```

If you want to run the tests from a specific folder, you need to copy `phpunit.xml.dist` as `phpunit.xml`
and [add your own suite](https://phpunit.de/manual/6.5/en/organizing-tests.html) in it:
```bash
$ phpunit -c app/phpunit.xml --testsuite="my_own_test_suite"
```

If you want to run the tests from a specific file:
```bash
$ phpunit -c phpunit.xml.dist src/Akeneo/UserManagement/Component/tests/integration/Updater/UserUpdaterIntegration.php
```

If you want to run a specific test, filter the name of the test:
```bash
$ phpunit -c phpunit.xml.dist src/Akeneo/UserManagement/Component/tests/integration/Updater/UserUpdaterIntegration.php --filter testSuccessfullyToCreateAUser
```

### Behat

Official website: http://behat.org/

`Behat` is a Behavior Driven Development (BDD) framework.
It is the PHP implementation of [Cucumber](https://cucumber.io/), the reference of the `Gherkin` language.
Akeneo PIM uses it for back-end acceptance and end-to-end tests.

It is not possible to run all the tests at once, as there is no `default` profile in the configuration file.
There are currently 2 profiles: `legacy` (our old deprecated end to end tests), and `acceptance` (for back-end acceptance tests).
So it is mandatory to specify which profile is to be run.

The configuration is defined in the file [behat.yml.dist](https://github.com/akeneo/pim-community-dev/blob/master/behat.yml.dist).
You can copy this file as `behat.yml` to change the URLs used in the `legacy` profile, so it fits your Akeneo PIM installation.

If you want to run all the tests of a profile (in this example, the {acceptance{ profile):
```bash
$ vendor/bin/behat -p acceptance
```

If you want to use `docker-compose`:
```bash
$ docker-compose run php vendor/bin/behat -p acceptance
```

For each profile, you have at least one test suite. If you want to run the tests from a specific suite:
```bash
$ vendor/bin/behat -p acceptance -s volume-monitoring
```

If you want to run the tests from a specific folder:
```bash
$ vendor/bin/behat -p acceptance tests/features/pim/locale
```

If you want to run the tests from a specific file:
```bash
$ vendor/bin/behat -p acceptance tests/features/pim/locale/detach_locale_from_channel.feature
```

If you want to run a specific test, add the line where the test starts:
```bash
$ vendor/bin/behat -p acceptance tests/features/pim/locale/detach_locale_from_channel.feature:7
```

## Front-end

In this section, the front-end testing tools are never ran directly, but always through `Yarn` and the aliases defined
in the `scripts` section of the file [package.json](https://github.com/akeneo/pim-community-dev/blob/master/package.json#L5)

For more information about how to write front-end tests and prepare the PIM to run them, please read [Writing front-end tests](https://github.com/akeneo/pim-community-dev/blob/master/tests/front/WRITING-FRONT-TESTS.md)

### ESLint

Official website: https://eslint.org/

ESLint is a code-style tool that detects and fixes JavaScript issues.
Its configuration is defined in the file [.eslintrc](https://github.com/akeneo/pim-community-dev/blob/master/.eslintrc).

If you want to detect all code issues:
```bash
$ yarn run lint
```

If you want to use `docker-compose`:
```bash
$ docker-compose run --rm node yarn run lint
```

If you want to fix code issues:
```bash
$ yarn run lint-fix
```

### Jest

Official websites:
- Jest: https://jestjs.io/

`Jest` is a JavaScript testing platform. In Akeneo PIM, it is used for both unit and integration front-end tests.

If you want to run all the unit tests:
```bash
$ yarn run unit
```

If you want to run all the integration tests:
```bash
$ yarn run integration
```

If you want to use `docker-compose`:
```bash
$ docker-compose run --rm node yarn run unit
$ docker-compose run --rm node yarn run integration
```

You can run a specific set of tests, or even only one test. Examples below are provided for unit tests, but it works exactly the same for integration tests.

If you want to run the tests from a specific folder:
```bash
$ yarn run unit tests/front/unit/pimenrich 
```

If you want to run the tests from a specific file:
```bash
$ yarn run unit tests/front/unit/pimui/js/i18n2.unit.ts
```

If you want to run a specific test, specify the name of the test:
```bash
$ yarn run unit tests/front/unit/pimui/js/i18n2.unit.ts -t 'get label for existing translation' 
```

### Cucumber.js + Puppeteer

Official websites:
- Cucumber.js: https://github.com/cucumber/cucumber-js
- Puppeteer: https://github.com/GoogleChrome/puppeteer

`Cucumber.js` is a Behavior Driven Development (BDD) framework. It is the JavaScript implementation of [Cucumber](https://cucumber.io/), the reference of the `Gherkin` language.
Akeneo PIM uses it for front-end acceptance tests. It is associated with `Puppeteer` to controlling a headless Chrome or Chromium web browser.

If you want to run all the acceptance tests:
```bash
$ yarn run acceptance
```

If you want to use `docker-compose`:
```bash
$ docker-compose run --rm node yarn run acceptance
```

If you want to generate an HTML report:
```bash
$ docker-compose run --rm node yarn run acceptance-html-report
```

If you want to run the acceptance tests from a specific folder:
```bash
$ yarn run acceptance tests/features/volume-monitoring
```

If you want to run the acceptance tests from a specific file:
```bash
$ yarn run acceptance tests/features/volume-monitoring/monitor_attribute_per_family_volume.feature
```

If you want to run a specific acceptance test, add the line where the test starts:
```bash
$ yarn run acceptance tests/features/volume-monitoring/monitor_attribute_per_family_volume.feature:7
```
