##
## Target used run command related on team work assistant bounded context
##

.PHONY: twa-coupling
twa-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/TeamworkAssistant/.php_cd.php src/Akeneo/Pim/WorkOrganization/TeamworkAssistant

