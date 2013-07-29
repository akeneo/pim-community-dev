Akeneo PIM Application
========================

Welcome to Akeneo PIM.

This document contains information on how to download, install, and start
using Akeneo PIM.

Important Note: this application is not production ready and is intendant for evaluation and development only!

Requirements
------------

Akeneo PIM requires Symfony 2, Doctrine 2 and PHP 5.3.3 or above.

Installation instructions:
-------------------------

### Using Composer

[As both Symfony 2 and Akeneo PIM use [Composer][2] to manage their dependencies, this is the recommended way to install Akeneo PIM.]

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s https://getcomposer.org/installer | php

- Clone git@github.com:akeneo/pim.git Akeneo PIM project with

    git clone git@github.com:akeneo/pim.git

- Go to app/config folder and create parameters.yml using parameters.yml.dist as example. Update database name and credentials
- Install Akeneo PIM dependencies with composer. If installation process seems too slow you can use "--prefer-dist" option.

    php composer.phar install

- Initialize application with install script : init-db.sh

After installation you can login as application administrator using user name "admin" and password "admin".

Checking your System Configuration
-------------------------------------

Before starting to code, make sure that your local system is properly
configured for a Symfony application.

Execute the `check.php` script from the command line:

    php app/check.php

Access the `config.php` script from a browser:

    http://your_domain/path/to/symfony/app/web/config.php

If you get any warnings or recommendations, fix them before moving on.


[1]:  http://symfony.com/doc/2.1/book/installation.html
[2]:  http://getcomposer.org/
