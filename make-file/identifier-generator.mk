.PHONY: identifier-generator-front-check
identifier-generator-front-check:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:check
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator test:unit:run

.PHONY: identifier-generator-front-fix
identifier-generator-front-fix:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:fix

identifier-generator-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run components/identifier-generator/back/tests/Specification

identifier-generator-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/identifier-generator/back/tests/.php_cs.php --allow-risky=yes
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--level 3 \
		--configuration components/identifier-generator/back/tests/phpstan.neon

identifier-generator-lint-back_fix:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=components/identifier-generator/back/tests/.php_cs.php --allow-risky=yes
