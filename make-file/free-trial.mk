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
