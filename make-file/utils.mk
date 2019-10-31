# This function will execute a command (first argument) and add command options (second argument) if CI=1
# You need to use this function to configure your targets if they need specific options to be ran on the CI
#
# $(call execute, command, options)
#
# Example: $(call execute, vendor/bin/phpspec run, --format=junit > spec.xml)
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

define execute
    $(if $(filter $(CI),1),$(1) $(2), $(1))
endef

# This function execution a command if the var CI is set to "1".
#
# Example: $(call execute_on_ci_only, my/script.sh) where my/script.sh is the script you want to run only in the CI.

define execute_on_ci_only
    $(if $(filter $(CI),1), $(1))
endef
