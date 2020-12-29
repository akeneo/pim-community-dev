Akeneo PIM Enterprise Application
=================================
Welcome to Akeneo PIM Enterprise product.

This repository is used to develop the Akeneo PIM Enterprise product.
Practically, it means the Akeneo PIM bundles are present in the src/ directory.

Here is [the documentation](https://docs.akeneo.com/5.0/install_pim/docker/installation_docker.html) to install the PIM using Docker & make.

## how to bootstrap a PIM

 * make php-image-dev
 * make dependencies
 * make pim-dev

## Make utility

### useful make targets

 * help:            documentation of available targets
 * pim-dev:         install a PIM using the development environement
 * dependencies:    install front and back dependencies
 * vendor:          launch composer update
 * yarn.lock:       launch yarn install
 * database:        flush the database and install a icecat sample catalog
 * css:             build the CSS

### environment variables

It is possible to alter the execution of the targets using environment variables:

    * `XDEBUG_MODE=debug` enable XDebug
    * `APP_ENV=prod` set the Sf environment to "prod"
    * `PIM_CONTEXT=test` see below


The following variables are also available (with their default value):

    * `PHP_CONF_DATE_TIMEZONE=UTC`
    * `PHP_CONF_MAX_EXECUTION_TIME=60`
    * `PHP_CONF_MEMORY_LIMIT=512M`
    * `PHP_CONF_OPCACHE_VALIDATE_TIMESTAMP=0`
    * `PHP_CONF_MAX_INPUT_VARS=1000`
    * `PHP_CONF_UPLOAD_LIMIT=40M`
    * `PHP_CONF_MAX_POST_SIZE=40M`
    * `PHP_CONF_DISPLAY_ERRORS=0`
    * `PHP_CONF_DISPLAY_STARTUP_ERRORS=0`

### context oriented targets

When working on a particular project, it is useful to get context only targets such as launching tests for the current project. It is possible to add these targets in a file in the `make-files` directory. Setting the `PIM_CONTEXT` environment variable with the name of that file (omitting the extension) will make the main file to include it.

The following line will include `make-files/my_project.mk`:

    $> PIM_CONTEXT=my_project make something 

It might be a good idea to set once for all that variable in the shell environment by adding this line in the `.bashrc` configuration file:

    export PIM_CONTEXT=my_project

### the test contexts

When creating a new context Makefile dedicated to testing, please add a `include make-files/test.mk` on top of your own to be able to depend on the generic targets. To be able to get your tests launched by the CI, add this line under the according `test` target:

    PIM_CONTEXT=my_context $(MAKE) my_test_suite

## Working with CE & EE

When working on a PR that involves a branch on both CE & EE, here is a command to makes composer to install the right CE branch:

    docker-compose run -u www-data --rm php php -d memory_limit=4G /usr/local/bin/composer require akeneo/pim-community-dev:dev-<BRANCH_NAME>

