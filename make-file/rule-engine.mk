##
## Target used run command related on rule engine bounded context
##

.PHONY: rule-engine-coupling
rule-engine-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/RuleEngine/.php_cd.php src/Akeneo/Pim/Automation/RuleEngine
