_APPS_YARN_RUN = $(YARN_EXEC) run --cwd=src/Akeneo/Apps/front/

# Tests

apps-front-tests:
	$(_APPS_YARN_RUN) jest

apps-front-codestyle-check:
	$(_APPS_YARN_RUN) tslint
	$(_APPS_YARN_RUN) prettier --check

# Development

apps-front-tests-watch:
	$(_APPS_YARN_RUN) jest --watchAll

apps-front-codestyle-fix:
	$(_APPS_YARN_RUN) tslint --fix
	$(_APPS_YARN_RUN) prettier
