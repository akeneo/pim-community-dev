DOCKER ?= false
PHP_EXEC :=php

ifeq ($(DOCKER),true)
	PHP_EXEC=docker-compose exec -u docker fpm php
endif

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo ""
	@echo "Akeneo Pim Enterprise Dev available targets:"
	@echo ""
	@echo "If you want to use docker, use an environment variable DOCKER=true"
	@echo ""
	@grep -E '^.PHONY:.*##.*' $(MAKEFILE_LIST) | cut -c9- | sort | awk 'BEGIN {FS = " ## "}; {printf "%-30s %s\n", $$1, $$2}'

### Coupling detection
.PHONY: reference-entity-coupling ## Run the coupling detector on Reference Entity
reference-entity-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/ReferenceEntity/tests/back/.php_cd.php src/Akeneo/ReferenceEntity/back

.PHONY: franklin-insights-coupling ## Run the coupling detector on Suggest data
franklin-insights-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/FranklinInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/FranklinInsights

.PHONY: asset-coupling ## Run the coupling detector on Asset
asset-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Asset/.php_cd.php src/Akeneo/Asset

.PHONY: twa-coupling ## Run the coupling detector on Teamwork Assistant
twa-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/TeamworkAssistant/.php_cd.php src/Akeneo/Pim/WorkOrganization/TeamworkAssistant

.PHONY: workflow-coupling ## Run the coupling detector on Workflow
workflow-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/Workflow/.php_cd.php src/Akeneo/Pim/WorkOrganization/Workflow

.PHONY: rule-engine-coupling ## Run the coupling detector on Rule Engine
rule-engine-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/RuleEngine/.php_cd.php src/Akeneo/Pim/Automation/RuleEngine

.PHONY: permission-coupling ## Run the coupling detector on Permission
permission-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Permission/.php_cd.php src/Akeneo/Pim/Permission

.PHONY: coupling ## Run the coupling detector on everything
coupling: twa-coupling asset-coupling franklin-insights-coupling reference-entity-coupling rule-engine-coupling workflow-coupling permission-coupling
