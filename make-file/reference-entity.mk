##
## Target used run command related on reference entity bounded context
##

.PHONY: reference-entity-coupling
reference-entity-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/ReferenceEntity/tests/back/.php_cd.php src/Akeneo/ReferenceEntity/back

