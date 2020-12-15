# Define here the targets your team needs to work and the targets to run tests on CI
#
# Which kind of tests do/should we have in the PIM?
# =================================================
# - lint (back and front)
# - unit (back and front)
# - acceptance (back and front)
# - integration (back and front)
# - end to end
#
# You should at least define a target for every kind of tests we previously listed with the following pattern:
#
# bounded-context-(lint|unit|acceptance|integration|end-to-end)-(back|front)
#
# Examples: asset-unit-back, asset-acceptance-front, asset-integration-back.
#
# How to define targets with a specfic configuration for the CI?
# ==============================================================
# For instance phpspec does not support multiple formats that means you can run the same command on the CI and locally.
# If you need to do that you can use an environment variable named CI which is a "boolean" (don't forget, env vars are strings).
# If its value equals 1 run the command configured for the CI otherwise configure it to run it locally.
#
# Example:
# -------
# target-name:
# ifeq ($(CI),true)
#	execute a command on the CI
# else
#	execute a command locally
# endif
#
# How can I can run test tools with specfic configuration?
# ========================================================
# You can define an environement variable to make targets configurable. Let's name O for *O*ption but it does not matter.
#
# Example:
# --------
# bounded-context-unit-back: var/tests/phpspec
#	 ${PHP_RUN} vendor/bin/phpspec run $(O)
#
# Run a spec
# ----------
#
# make bounded-context-unit-back O=my/spec.php
#
# How to run them on the CI?
# ==========================
# You should add them as dependency of the "main targets" which in `make-file/test.mk`.
# You should have a look to `coupling-back` this target does not run a command but only depends on other ones.
#
# /!\ CAUTION /!\ By default some "main targets" run commands because of our legacy code (hard to reconfigure all tools).
# Make sure the tests run by the targets defined here does not run by the main targets too
#

include make-file/test.mk

.PHONY: rule-engine-coupling-back
rule-engine-coupling-back: #Doc: launch PHP coupling detector for rule-engine
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/RuleEngine/.php_cd.php src/Akeneo/Pim/Automation/RuleEngine

.PHONY: rule-engine-unit-back
rule-engine-unit-back: var/tests/phpspec #Doc: launch PHPSec unit tests for rule-engine
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T -u www-data --rm php php vendor/bin/phpspec run --config tests/back/Pim/Automation/Specification/RuleEngine/phpspec.yml.dist --format=junit > var/tests/phpspec/rule-engine.xml
else
	$(PHP_RUN) vendor/bin/phpspec run --config tests/back/Pim/Automation/Specification/RuleEngine/phpspec.yml.dist $(O)
endif

.PHONY: rule-engine-acceptance-back
rule-engine-acceptance-back: var/tests/behat/rule-engine-acceptance #Doc: launch Behat acceptance tests for rule-engine
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Pim/Automation/RuleEngine/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/rule-engine-acceptance --format progress --out std --colors $(O)

.PHONY: rule-engine-integration-back
rule-engine-integration-back: var/tests/behat/rule-engine-integration #Doc: launch Behat integration tests for rule-engine
	APP_ENV=test $(PHP_RUN) vendor/bin/behat --config src/Akeneo/Pim/Automation/RuleEngine/tests/behat.yml --profile integration --format pim --out var/tests/behat/rule-engine-integration --format progress --out std --colors $(O)

.PHONY: rule-engine-unit-front
rule-engine-unit-front: #Doc: launch YARN jest for rule-engine
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/Automation/RuleEngine/front jest --ci $(O)

.PHONY: rule-engine-lint-front
rule-engine-lint-front: #Doc: launch YARN linter for rule-engine
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/Automation/RuleEngine/front lint

.PHONY: rule-engine-types-check-front
rule-engine-types-check-front: #Doc: launch YARN types-check for rule-engine
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/Automation/RuleEngine/front types-check

.PHONY: rule-engine-prettier-check-front
rule-engine-prettier-check-front: #Doc: launch YARN prettier-check for rule-engine
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/Automation/RuleEngine/front prettier-check
