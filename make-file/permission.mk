##
## Target used run command related on permission bounded context
##

.PHONY: permission-coupling-back
permission-coupling-back: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Permission/.php_cd.php src/Akeneo/Pim/Permission
