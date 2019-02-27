##
## Target used run command related on permission bounded context
##

.PHONY: permission-coupling
permission-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Permission/.php_cd.php src/Akeneo/Pim/Permission
