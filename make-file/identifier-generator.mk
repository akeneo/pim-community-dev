IDENTIFIER_GENERATOR_PATH ?= components/identifier-generator

.PHONY: identifier-generator-front-check
identifier-generator-front-check:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:check
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator test:unit:run

.PHONY: identifier-generator-front-fix
identifier-generator-front-fix:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:fix

.PHONY: identifier-generator-unit-front
identifier-generator-unit-front: yarn-policies
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator test:unit:run --ci --coverage ${O}

.PHONY: identifier-generator-unit-back
identifier-generator-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run $(IDENTIFIER_GENERATOR_PATH)/back/tests/Specification

.PHONY: identifier-generator-fix-lint-back
identifier-generator-fix-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=$(IDENTIFIER_GENERATOR_PATH)/back/tests/.php_cs.php --allow-risky=yes

.PHONY: identifier-generator-lint-back
identifier-generator-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=$(IDENTIFIER_GENERATOR_PATH)/back/tests/.php_cs.php --allow-risky=yes --dry-run
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--level max \
		--configuration components/identifier-generator/back/tests/phpstan.neon \
		$(IDENTIFIER_GENERATOR_PATH)/back/src/Infrastructure
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--level max \
		--configuration components/identifier-generator/back/tests/phpstan.neon \
		$(IDENTIFIER_GENERATOR_PATH)/back/src/Domain $(IDENTIFIER_GENERATOR_PATH)/back/src/Application
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--level 0 \
		--configuration components/identifier-generator/back/tests/phpstan.neon \
		$(IDENTIFIER_GENERATOR_PATH)/back/tests

.PHONY: identifier-generator-acceptance-back
identifier-generator-acceptance-back:
	$(PHP_RUN) vendor/bin/behat --config $(IDENTIFIER_GENERATOR_PATH)/back/tests/behat.yml --suite=acceptance --format pim --out var/tests/behat/identifier-generator --format progress --out std --colors $(O)

.PHONY: identifier-generator-coupling-back
identifier-generator-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect \
		--config-file=$(IDENTIFIER_GENERATOR_PATH)/back/tests/.php_cd.php

.PHONY: identifier-generator-phpunit-back
identifier-generator-phpunit-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--testsuite Identifier_Generator_PhpUnit \
		--order-by random \
		--log-junit var/tests/phpunit/phpunit_identifier_generator_end_to_end.xml ${O}
