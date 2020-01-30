# Rector Rules for automatic custom code migration

Since Akeneo PIM 4.0, Rector rules replace sed commands to automatically fix
custom code migration in case of class or namespace renaming.

## How to generate Rector Rules for your current PIM
Usage: php vendor/akeneo/pim-community-dev/std-build/migration/get_renamed_php_classes.php <tag> <additional_file> <additional_file> > final_rector.yaml

### Example:
$ php vendor/akeneo/pim-community-dev/std-build/migration/get_renamed_php_classes.php 3.2 vendor/akeneo/pim-community-dev/std-build/migration/32_to_40/rector.yaml std-build/migration/32_to_40/rector_ee_manual.yaml > std-build/migration/32_to_40/rector.yaml

The command above will generate the configuration based on moved PHP classes
and namespaces into the std-build/migration/32_to_40/rector.yaml, while adding
Rector configurations coming from the CE and another defined by hand.

## How to apply Rector Rules on a PIM project

1. Make sure the previously generated rector.yaml file has been copied at the root of the PIM project
2. composer require --dev rector/rector
3. vendor/bin/rector scan-fatal-errors src/
4. vendor/bin/rector process src/
