.PHONY: enrichment-category-acceptance-back
enrichment-category-acceptance-back: var/tests/behat/enrichment-category
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/create_a_category.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/edit_a_category.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/export_categories_csv.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/export_categories_xlsx.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/import_categories.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/list_categories.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy tests/legacy/features/pim/enrichment/category/remove_a_category.feature
