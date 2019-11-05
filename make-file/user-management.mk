##
## Target used run command related on User management Bounded context
##

.PHONY: user-management-coupling-back
user-management-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement
