Akeneo PIM Application
======================
Welcome to Akeneo PIM Product.

This repository is used to develop the Akeneo PIM product.
Practically, it means the Akeneo PIM bundles are present in the src/ directory.

**If you want to create a new PIM project based on Akeneo PIM, please use http://www.github.com/akeneo/pim-community-standard**

If you want to contribute to the Akeneo PIM (and we will be pleased if you do!), you can fork
this repository and submit a pull request.

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/akeneo/pim-community-dev/badges/quality-score.png?s=05ef3d5d2bbfae2f9a659060b21711d275f0c1ff)](https://scrutinizer-ci.com/g/akeneo/pim-community-dev/)

Requirements
------------
## System
 - PHP 5.4.* above 5.4.4
 - PHP Modules:
    - php5-curl
    - php5-gd
    - php5-intl
    - php5-mysql
    - php5-mcrypt
    - php-apc for PHP 5.4 (opcode and data cache)
    - php5-apcu for PHP 5.5 (for data cache, as opcode cache usually included)
 - PHP memory_limit at least at 256 MB on Apache side and 728 MB on CLI side (needed for installation, can be lowered to 512MB after installation for PHP-CLI)
 - MySQL 5.1 or above
 - Apache mod rewrite enabled
 - Java JRE (for compressing the JavaScript via YUI Compressor)

## Web browsers
 - tested: Chrome & Firefox
 - should work: IE 10, Safari
 - will not work: IE < 10

Installation instructions
-------------------------
To install Akeneo PIM for a PIM project or for evaluation, please follow:
http://docs.akeneo.com/installation/installation_workstation.html

The following installation overview is for contributing to Akeneo PIM, not for project purpose.

## Installation overview:
* Install files and DB content

    $ curl -s https://getcomposer.org/installer | php
    $ git clone git@github.com:akeneo/pim-community-dev.git
    $ cd pim-community-dev
    $ php ../composer.phar install
    $ php app/console pim:install --env=dev
    $ php app/console cache:clear --env=dev

* Create a VirtualHost pointing to your pim-community-dev/web directory and a matching hostname
* Go to http://<my-hostname>/app_dev.php to access your dev environment

Note: using the "--prefer-dist" option on composer install can speed up
the installation by looking into your local Composer cache.

Note: The pim:install command can be executed several times if you need to reinit your db or redeploy your assets.
You just have to use the `--force` option.
By default, this script initializes the dev environment.

### Add translation packs (optional)

You can download translation packs from crowdin:
- http://crowdin.net/project/akeneo
- http://crowdin.net/project/oro-platform

The Akeneo PIM archive contains the following directories tree: `<locale>/<version>/<translation_directories>`
You just have to paste the <translation_directories> in your app/Resources/ directory.

For Oro Platform, the archive contains the same directories tree except the version directory which is removed.

Write permission for the HTTP server
------------------------------------

You must give write permission to the Apache user on the following directories:
- app/cache
- app/logs
- app/entities
- app/import
- app/export
- app/emails
- web/bundles
- app/uploads/product
- app/archive

Configure crontab
-----------------

To ensure that completeness is as up to date as possible, you can configure the following crontab
line:

    */2 * * * * php app/console pim:completeness:calculate > /tmp/completeness.log

In case you import data without running the versioning system in real time, you can make sure
that versioning is recalculated appropriately with this crontab line (assuming you filled the
version pending table with the adequate information):

    */5 * * * * php app/console pim:versioning:refresh > /tmp/versioning.log

