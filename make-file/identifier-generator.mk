.PHONY: identifier-generator-front-check
identifier-generator-front-check:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:check
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator test:unit:run

.PHONY: identifier-generator-front-fix
identifier-generator-front-fix:
	$(YARN_RUN) workspace @akeneo-pim-community/identifier-generator lint:fix
