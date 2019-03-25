##
## Target used run command related on Structure Bounded context
##

.PHONY: structure-coupling
structure-coupling:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Structure/.php_cd.php src/Akeneo/Pim/Structure
