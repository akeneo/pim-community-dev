include make-file/test.mk

.PHONY: shared-catalog-lint-back
shared-catalog-lint-back:
	${PHP_RUN} vendor/bin/rector process --dry-run --config=src/Akeneo/SharedCatalog/tests/back/rector.php

shared-catalog-lint-fix-back:
	${PHP_RUN} vendor/bin/rector process --config=src/Akeneo/SharedCatalog/tests/back/rector.php

.PHONY: pim-shared-catalog
pim-shared-catalog: #Doc: run docker-compose up, clean symfony cache, reinstall assets, build PIM CSS, run webpack dev & install shared_catalog_fixtures database
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) dsm
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog shared_catalog_fixtures" $(MAKE) database
	APP_ENV=dev $(PHP_RUN) bin/console akeneo:shared-catalog:fixtures --force
