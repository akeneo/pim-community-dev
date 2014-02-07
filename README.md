Akeneo PIM Application
======================
Welcome to Akeneo PIM Product.

This repository is used to develop the Akeneo PIM product.
Practically, it means the Akeneo PIM bundles are present in the src/ directory.

If you want to contribute to the Akeneo PIM (and we will be pleased if you do!), you can fork
this repository and submit a pull request.

Important note: the current version of this application is not production ready and is intended for evaluation and development purposes only!

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/akeneo/pim-community-dev/badges/quality-score.png?s=05ef3d5d2bbfae2f9a659060b21711d275f0c1ff)](https://scrutinizer-ci.com/g/akeneo/pim-community-dev/)

Requirements
------------
## System
 - PHP 5.4.* above 5.4.4 (Akeneo PIM has not been yet tested with PHP 5.5)
 - PHP Modules:
    - php5-curl
    - php5-gd
    - php5-intl
    - php5-mysql
    - php5-mcrypt
    - php-apc (opcode and data cache)
 - PHP memory_limit at least at 256 MB on Apache side and 512 MB on CLI side
 - MySQL 5.1 or above
 - Apache mod rewrite enabled
 - Java JRE (for compressing the JavaScript via YUI Compressor)

Akeneo PIM is based on Symfony 2, Doctrine 2 and [Oro Platform][3].
These dependencies will be installed automatically with [Composer][2].

## Web browsers
 - tested: Chrome & Firefox
 - supported: IE 10, Safari
 - not supported: IE < 10

Installation instructions
-------------------------
## Using Composer to install dependencies

This is the recommended way to install Akeneo PIM.

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    $ curl -s https://getcomposer.org/installer | php

### Clone Akeneo PIM project with:

    git clone git@github.com:akeneo/pim-community-dev.git

Now, you can go to your pim project directory.

    $ cd pim-community-dev

### Install Akeneo PIM dependencies with Composer

Due to some limitations of Oro Platform, you **MUST** create your database before launching composer.

    $ php ../composer.phar install

Note that using the "--prefer-dist" option can speed up
the installation by looking into your local Composer cache.

### Add translation packs (optional)

You can download translation packs from crowdin:
- http://crowdin.net/project/akeneo
- http://crowdin.net/project/oro-platform

The Akeneo PIM archive contains the following directories tree: <locale>/<version>/<translation_directories>
You just have to paste the <translation_directories> in your app/Resources/ directory.

For Oro Platform, the archive contains the same directories tree except the version directory which is removed.

### Initialize data and assets

    $ php app/console pim:install --env=dev

Note: This script can be executed several times if you need to reinit your db or redeploy your assets.
You just have to use the `--force` option.
By default, this script initializes the dev environment.

### Clear the cache to finalize the installation

    $ php app/console cache:clear --env=dev

Create the Apache Virtual host
------------------------------

```
<VirtualHost *:80>
    ServerName akeneo-pim.local

    DocumentRoot /path/to/your/pim/installation/web/
    <Directory /path/to/your/pim/installation/web/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/akeneo-pim_error.log

    # Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
    LogLevel warn
    CustomLog ${APACHE_LOG_DIR}/akeneo-pim_access.log combined
</VirtualHost>
```
Do not forget to change the "/path/to/your/pim/installation/web" to the full path to
the web directory inside your Akeneo PIM installation directory.

Now, you just have to add your host to hosts file `/etc/hosts`:

```
127.0.0.1 localhost akeneo-pim.local
```

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

Checking your System Configuration
----------------------------------

Before starting to contribute to Akeneo, make sure that your system is properly
configured for a Symfony application.

Execute the `check.php` script from the command line:

    php app/console pim:install --force --task=check

If you get any warnings or recommendations, fix them before moving on.

Connect to your PIM application
-------------------------------

Go to http://akeneo-pim.local/ for production mode or http://akeneo-pim.local/app_dev.php for development mode.
Note: If you want to use development mode, do not forget to launch ./install.sh all dev

You can now connect as Akeneo administrator with the following credentials:
- username: "admin"
- password: "admin"


Generating a clean database
---------------------------

By default, when you install the PIM, the database is preconfigured with demo data.

If you want to get only the bare minimum of data to have a clean but functional PIM,
just change the following config line in app/config/parameters.yml:

```
    installer_data: PimInstallerBundle:minimal
```

Then relaunch the install.sh script with the db option:

$ php app/console pim:install --force --env=prod --task=db

Known issues
------------
 - when cleaning up the cache by hand (rm -rf app/cache/*), error about `Oro\\Bundle\\UserBundle\\Entity\\User::$field_catalogLocale` can occur. In this case, a proper cache:clear command is required, as it will warm a non-corrupted cache:
`php app/console cache:clear`
 - with XDebug on, the default value of max_nesting_level (100) is too low and can make the ACL loading fail (which causes 403 HTTP response code on every application screen, even the login screen). A working value is 500:
`xdebug.max_nesting_level=500`

 - not enough memory can cause the JS routing bundle to fail with a segmentation fault. Please check with `php -i | grep memory` that you have enough memory according to the requirements

 - some segmentation fault can be caused as well by the circular references collector. You can disable it with the following setting in your php.ini files:
`zend.enable_gc = 0`

 - When installing with `php composer.phar create-project...` command, error about `Unable to parse file "<path>/Resources/config/web.xml".`. It seems an external issue related to libxml, you can downgrade to `libxml2.x86_64 0:2.6.26-2.1.21.el5_9.1`. Look at: http://www.akeneo.com/topic/erreur-with-php-composer-phar-beta4/ for more informations.


[1]:  http://symfony.com/doc/2.1/book/installation.html
[2]:  http://getcomposer.org/
[3]:  http://www.orocrm.com/oro-platform 


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/akeneo/pim-community-dev/trend.png)](https://bitdeli.com/free "Bitdeli Badge")
