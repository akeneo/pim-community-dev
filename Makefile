DOCKER ?= false
PHP_EXEC :=php

ifeq ($(DOCKER),true)
	PHP_EXEC=docker-compose exec -u docker fpm php
endif

### Coupling detection
.PHONY: reference-entity-coupling
reference-entity-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/ReferenceEntity/tests/back/.php_cd.php src/Akeneo/ReferenceEntity/back

.PHONY: suggest-data-coupling
suggest-data-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/SuggestData/tests/back/.php_cd.php src/Akeneo/Pim/Automation/SuggestData

.PHONY: asset-coupling
asset-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Asset/.php_cd.php src/Akeneo/Asset

.PHONY: twa-coupling
twa-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/TeamworkAssistant/.php_cd.php src/Akeneo/Pim/WorkOrganization/TeamworkAssistant

.PHONY: workflow-coupling
workflow-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/Workflow/.php_cd.php src/Akeneo/Pim/WorkOrganization/Workflow

.PHONY: rule-engine-coupling
rule-engine-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/RuleEngine/.php_cd.php src/Akeneo/Pim/Automation/RuleEngine

.PHONY: permission-coupling
permission-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Permission/.php_cd.php src/Akeneo/Pim/Permission

.PHONY: coupling
coupling: twa-coupling asset-coupling suggest-data-coupling reference-entity-coupling rule-engine-coupling workflow-coupling permission-coupling
