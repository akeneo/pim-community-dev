_APPS_YARN_RUN = $(YARN_EXEC) run --cwd=src/Akeneo/Apps/front/

# Tests

apps-coupling:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Apps/back/tests/.php_cd.php src/Akeneo/Apps/back

apps-front-tests:
	$(_APPS_YARN_RUN) jest

apps-front-lint:
	$(_APPS_YARN_RUN) tslint
	$(_APPS_YARN_RUN) prettier --check

# Development

apps-front-tests-watch:
	$(_APPS_YARN_RUN) jest --watchAll --coverage

apps-front-lint-fix:
	$(_APPS_YARN_RUN) tslint --fix
	$(_APPS_YARN_RUN) prettier --write
