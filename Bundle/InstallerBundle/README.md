OroInstallerBundle
==================

Web installer for OroCRM. Inspired by [Sylius](https://github.com/Sylius/SyliusInstallerBundle).

To run the installer on existing setup, you need to update parameters.yml file:
``` yaml
# ...
session_handler: ~
installed: ~
```

## Usage ##
If you are using distribution package, you will be redirected to installer page automatically.

Otherwise, following installation instructions offered:
``` bash
$ git clone https://github.com/orocrm/crm-application.git
$ cd crm-application
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
$ php app/console oro:install
```