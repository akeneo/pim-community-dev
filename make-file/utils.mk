# This function will set the var "O" with the given argument if the var CI is set to "1".
# You need to use this function to configure your targets to run them on the CI:
# $(call configure_ci_options, XXX) where XXX is the target options.
#
# Example: $(call configure_ci_options, --format=junit > spec.xml)
#
# How to run tests? Let's take an example: run asset manager unit tests.
#
# Locally, I want the default formatter, I use the following command:
# make asset-manager-unit-back
#
# On the CI, I want to generate a JUnit file, I use the following command:
# make asset-manager-unit-back CI=1
#
# CAUTION:
#  - use this function if your test tools do not support mutiple output format
#  - the command in your target must use the var O
#
# unit-back:
#	$(PHP_RUN) vendor/bin/phpspec run $(O)

define configure_ci_options
    $(if $(filter $(CI),1),$(eval O=$(1)))
endef

# This function execution a command if the var CI is set to "1".
#
# Example: $(call execute_on_ci_only, my/script.sh) where my/script.sh is the script you want to run only in the CI.

define execute_on_ci_only
    $(if $(filter $(CI),1), $(1))
endef
