Akeneo PIM Enterprise Application
=================================
Welcome to Akeneo PIM Enterprise product.

This repository is used to develop the Akeneo PIM Enterprise product.
Practically, it means the Akeneo PIM bundles are present in the src/ directory.

Here is [the documentation](https://docs.akeneo.com/latest/install_pim/docker/installation_docker.html) to install the PIM using Docker & make.

## useful make targets

 * pim-dev: install a PIM using the development environement
 * vendor: launch composer update
 * yarn.lock: launch yarn install
 * database: flush the database and install a icecat sample catalog

## context oriented targets

When working on a particular project, it is useful to get context only targets such as launching tests for the project only. It is possible to add these targets in a file in the `make-files` directory. Setting the `PIM_CONTEXT` environment variable with the name of that file (omitting the extension) will make the main file to include it.

The following line will include `make-files/my_project.mk`:

    $> PIM_CONTEXT=my_project make something 

It might be a good idea to set once for all that variable in the shell environment by adding this line in the `.bashrc` configuration file:

    export PIM_CONTEXT=my_project
