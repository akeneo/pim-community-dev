DATABASE_CATALOG_FREE_TRIAL ?= src/Akeneo/FreeTrial/back/Infrastructure/Symfony/Resources/fixtures/free_trial_catalog

.PHONY: trial-dev
trial-dev: #Doc: run docker-compose up, clean symfony cache, run webpack dev & install free_trial_catalog database in dev environment
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	APP_ENV=dev $(MAKE) assets
	$(MAKE) css
	$(MAKE) front-packages
	$(MAKE) javascript-dev
	cd $(PIM_SRC_PATH) && docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog $(DATABASE_CATALOG_FREE_TRIAL)" $(MAKE) database
	APP_ENV=dev $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin admin@example.com John Doe en_US

.PHONY: free-trial-unit-back
free-trial-unit-back: #Doc: launch PHPSec for the free trial
	$(DOCKER_COMPOSE) run --rm php sh -c "cd tria && php ../vendor/bin/phpspec run --config=phpspec.yml.dist $(O)"

.PHONY: free-trial-integration-back
free-trial-integration-back: #Doc: launch PHPUnit integration test for the free trial
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite FreeTrial_Integration_EE $(O)

.PHONY: free-trial-tests
free-trial-tests: free-trial-unit-back free-trial-integration-back
