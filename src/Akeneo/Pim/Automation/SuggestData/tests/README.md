# How to run the tests of the SuggestData bounded context

This documentation is only about the specificity of the SuggestData bounded context.
For a general documentation about running the tests of the PIM, please refer to [this file](https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/RUNNING_THE_TESTS.md).

## Back-end tests

### PHP Coding Standards Fixer

The SuggestData bounded context contains its own `php-cs-fixer` configuration, more strict than the basic one used on the rest of the PIM.

You can run it in dry mode (no modification of the code) with the following command, from the root of the PIM:
```bash
$ vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Pim/Automation/SuggestData/tests/back/.php_cs.php
```

To apply the changes to your code, remove the `--dry-run` option.

### PHP Code Sniffer

PHP Code Sniffer is installed in the PIM, but was not used. We have  now a configuration file placed at the root of the PIM in `phpcs.xml.dist`.
PHP Code Sniffer configuration file must be placed at the root of the project, as it has no option to specify a custom path.

You can run it (no modification will be done to the code) with the following command, from the root of the PIM:
```bash
$ vendor/bin/phpcs
```

To apply the changes to your code, use the code beautifier provided by PHP Code Sniffer:
```bash
$ vendor/bin/phpcbf
```
