##
## Target used run command related on team work assistant bounded context
##

.PHONY: twa-coupling
twa-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/TeamworkAssistant/.php_cd.php src/Akeneo/Pim/WorkOrganization/TeamworkAssistant

.PHONY: twa-behat-legacy
twa-behat-legacy:
	F=tests/legacy/features/pim/work-organization/teamwork-assistant $(MAKE) behat-legacy

