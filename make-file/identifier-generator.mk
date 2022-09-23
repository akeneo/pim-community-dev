.PHONY: identifier-generator-front-check
identifier-generator-front-check:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:check
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator test:unit:run

.PHONY: identifier-generator-front-fix
identifier-generator-front-fix:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:fix

identifier-generator-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run components/identifier-generator/back/tests/Specification

identifier-generator-fix-cs-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=components/identifier-generator/back/.php_cs.php

