##
## Target used run command related on workflow bounded context
##

.PHONY: workflow-coupling
workflow-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/Workflow/.php_cd.php src/Akeneo/Pim/WorkOrganization/Workflow
