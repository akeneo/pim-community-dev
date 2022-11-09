var/tests/%: #Doc: run the selected test
	$(DOCKER_COMPOSE) run --rm php mkdir -p $@

.PHONY: find-legacy-translations
find-legacy-translations: #Doc: run find_legacy_translations.sh script
	vendor/akeneo/pim-community-dev/.circleci/find_legacy_translations.sh

.PHONY: coupling-back
coupling-back: #Doc: launch all coupling detector tests
	PIM_CONTEXT=twa $(MAKE) twa-coupling-back
	PIM_CONTEXT=data-quality-insights $(MAKE) data-quality-insights-coupling-back
	PIM_CONTEXT=reference-entity $(MAKE) reference-entity-coupling-back
	PIM_CONTEXT=asset-manager $(MAKE) asset-manager-coupling-back
	PIM_CONTEXT=rule-engine $(MAKE) rule-engine-coupling-back
	PIM_CONTEXT=workflow $(MAKE) workflow-coupling-back
	PIM_CONTEXT=permission $(MAKE) permission-coupling-back
	PIM_CONTEXT=communication-channel $(MAKE) communication-channel-coupling-back
	PIM_CONTEXT=syndication $(MAKE) coupling-back
	PIM_CONTEXT=tailored-export $(MAKE) coupling-back
	PIM_CONTEXT=tailored-import $(MAKE) coupling-back
	PIM_CONTEXT=job-automation $(MAKE) coupling-back
	PIM_CONTEXT=channel $(MAKE) channel-coupling-back
	PIM_CONTEXT=performance-analytics $(MAKE) performance-analytics-coupling-back
	PIM_CONTEXT=table-attribute $(MAKE) table-attribute-coupling-back
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=upgrades/.php_cd.php upgrades/schema
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=upgrades/.php_cd.php upgrades/schema

### Static tests
static-back: check-pullup check-sf-services #Doc: launch PHP static analyzer & check Sf services
	PIM_CONTEXT=asset-manager $(MAKE) asset-manager-static-back
	PIM_CONTEXT=table-attribute $(MAKE) table-attribute-static-back
	echo "Job done! Nothing more to do here..."

.PHONY: check-pullup
check-pullup: #Doc: check pullup
	${PHP_RUN} vendor/akeneo/pim-community-dev/bin/check-pullup

.PHONY: check-sf-services
check-sf-services: #Doc: check Sf services
	${PHP_RUN} bin/console lint:container

### Lint tests
.PHONY: lint-back
lint-back: #Doc: launch all PHP linter tests
	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 --rm php bin/console cache:warmup
	$(PHP_RUN) vendor/bin/phpstan analyse src/Akeneo/Pim/Automation --level 3
	$(PHP_RUN) vendor/bin/phpstan analyse src/Akeneo/Pim/Enrichment --level 2
	$(PHP_RUN) vendor/bin/phpstan analyse src/Akeneo/Pim/Permission --level 3
	$(PHP_RUN) vendor/bin/phpstan analyse src/Akeneo/Pim/Structure --level 8
	$(PHP_RUN) vendor/bin/phpstan analyse src/Akeneo/Pim/WorkOrganization --level 2
	PIM_CONTEXT=test $(MAKE) migration-lint-back
	PIM_CONTEXT=data-quality-insights $(MAKE) data-quality-insights-lint-back data-quality-insights-phpstan
	PIM_CONTEXT=reference-entity $(MAKE) reference-entity-lint-back
	PIM_CONTEXT=asset-manager $(MAKE) asset-manager-lint-back
	PIM_CONTEXT=communication-channel $(MAKE) communication-channel-lint-back
	PIM_CONTEXT=shared-catalog $(MAKE) shared-catalog-lint-back
	PIM_CONTEXT=syndication $(MAKE) lint-back
	PIM_CONTEXT=tailored-export $(MAKE) lint-back
	PIM_CONTEXT=tailored-import $(MAKE) lint-back
	PIM_CONTEXT=job-automation $(MAKE) lint-back
	PIM_CONTEXT=channel $(MAKE) channel-lint-back
	PIM_CONTEXT=performance-analytics $(MAKE) performance-analytics-lint-back
	PIM_CONTEXT=table-attribute $(MAKE) table-attribute-lint-back

	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs_ce.php

.PHONY: migration-lint-back
migration-lint-back:
	$(DOCKER_COMPOSE) run --rm php php vendor/bin/phpstan analyse -c upgrades/phpstan.neon

.PHONY: lint-front
lint-front: #Doc: launch all YARN linter tests
	$(YARN_RUN) lint
	PIM_CONTEXT=rule-engine $(MAKE) rule-engine-lint-front rule-engine-types-check-front rule-engine-prettier-check-front
    PIM_CONTEXT=table-attribute $(MAKE) table-attribute-lint-front table-attribute-prettier-check-front table-attribute-prettier-fix-front

### Unit tests
.PHONY: unit-back
unit-back: var/tests/phpspec community-unit-back #Doc: launch all PHPSec unit tests
	PIM_CONTEXT=reference-entity $(MAKE) reference-entity-unit-back
	PIM_CONTEXT=asset-manager $(MAKE) asset-manager-unit-back
	PIM_CONTEXT=tailored-export $(MAKE) unit-back
	PIM_CONTEXT=tailored-import $(MAKE) unit-back
	PIM_CONTEXT=job-automation $(MAKE) unit-back
	PIM_CONTEXT=channel $(MAKE) channel-unit-back
	PIM_CONTEXT=performance-analytics $(MAKE) performance-analytics-unit-back
	PIM_CONTEXT=table-attribute $(MAKE) table-attribute-unit-back
	$(DOCKER_COMPOSE) run -T --rm php sh -c "cd grth && php ../vendor/bin/phpspec run --format=junit --config phpspec-ee.yml > ../var/tests/phpspec/specs-ge.xml"
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T --rm php php vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
	vendor/akeneo/pim-community-dev/.circleci/find_non_executed_phpspec.sh
else
	${PHP_RUN} vendor/bin/phpspec run
endif

.PHONY: community-unit-back
community-unit-back: var/tests/phpspec #Doc: launch PHPSpec for PIM CE dev
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run  --format=junit > ../../../var/tests/phpspec/specs-ce.xml"
else
	$(DOCKER_COMPOSE) run --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run"
endif

.PHONY: unit-front
unit-front: #Doc: launch all JS unit tests
	$(YARN_RUN) unit
	PIM_CONTEXT=rule-engine $(MAKE) rule-engine-unit-front
    PIM_CONTEXT=table-attribute $(MAKE) table-attribute-unit-front

### Acceptance tests
.PHONY: acceptance-back
acceptance-back: var/tests/behat #Doc: launch Behat acceptance tests
	PIM_CONTEXT=reference-entity $(MAKE) reference-entity-acceptance-back
	PIM_CONTEXT=asset-manager $(MAKE) asset-manager-acceptance-back
	PIM_CONTEXT=rule-engine $(MAKE) rule-engine-acceptance-back
	PIM_CONTEXT=tailored-export $(MAKE) acceptance-back
	PIM_CONTEXT=tailored-import $(MAKE) acceptance-back
	PIM_CONTEXT=job-automation $(MAKE) acceptance-back
	PIM_CONTEXT=channel $(MAKE) channel-acceptance-back
	PIM_CONTEXT=enrichment-product $(MAKE) enrichment-product-acceptance-back
	PIM_CONTEXT=table-attribute $(MAKE) table-attribute-acceptance-back
	${PHP_RUN} vendor/bin/behat --config grth/src/Akeneo/Pim/TableAttribute/tests/back/behat.yml --suite=acceptance_ee --no-interaction --format=progress --strict
	${PHP_RUN} vendor/bin/behat -p acceptance --format pim --out var/tests/behat --format progress --out std --colors
	${PHP_RUN} vendor/bin/behat --config vendor/akeneo/pim-community-dev/behat.yml -p acceptance --no-interaction --format=progress --strict

.PHONY: acceptance-front
acceptance-front: MAX_RANDOM_LATENCY_MS=100 $(YARN_RUN) acceptance run acceptance ./tests/features #Doc: launch YARN acceptance

### Integration tests
.PHONY: integration-front
integration-front: #Doc: run YARN integration
	$(YARN_RUN) integration

.PHONY: pim-integration-back
pim-integration-back: #Doc: launch all PHPUnit integration tests that are not in a dedicated command for a context
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php PIM_Integration_Test
else
	@echo Run integration test locally is too long, please use the target defined for your bounded context \(ex: bounded-context-integration-back\)
endif

### Migration tests
.PHONY: migration-back
migration-back: var/tests/phpunit #Doc: launch PHP unit tests for migration
	cp vendor/akeneo/pim-community-dev/upgrades/schema/*.php upgrades/schema/.
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php PIM_Migration_Test ${O}
else
	@echo Migration files from CE copied into upgrade/ dir. Do not commit them.
	APP_ENV=test $(PHP_RUN) ./vendor/bin/phpunit -c . --testsuite PIM_Migration_Test ${O}
endif

### End to end tests
.PHONY: end-to-end-back
end-to-end-back: var/tests/phpunit #Doc: launch PHP unit end to end tests that are not in a dedicated command for a context
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php End_to_End
else
	@echo Run end to end test locally is too long, please use the target defined for your bounded context \(ex: bounded-context-end-to-end-back\)
endif

# How to debug a behat locally?
# -----------------------------
#
# Run the following command:
# make end-to-end-legacy O=my/feature/file.feature:23
#
# Don't forget to pass *O*ption to avoid to run the whole suite.
# Please add dependencies to this target and let it die

.PHONY: end-to-end-legacy
end-to-end-legacy: var/tests/behat #Doc: launch behat legacy tests
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_behat.sh $(SUITE)
	vendor/akeneo/pim-community-dev/.circleci/run_behat.sh critical
else
	$(PHP_RUN) vendor/bin/behat --strict -p legacy -s all ${O}
endif

.PHONY: test-database-structure
test-database-structure: #Doc: test database structure
	$(DOCKER_COMPOSE) run -e APP_DEBUG=1 --rm php bash -c 'bin/console pimee:database:inspect -f --db-name=akeneo_pim_test --env=test && bin/console pimee:database:diff --env=test'

.PHONY: end-to-end-front
end-to-end-front:
	$(DOCKER_COMPOSE_BIN) -f docker-compose-cypress.yml run --rm cypress
