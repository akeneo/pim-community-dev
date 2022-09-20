identifier-generator-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run components/identifier-generator/back/tests/Specification

identifier-generator-fix-cs-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=components/identifier-generator/back/.php_cs.php

