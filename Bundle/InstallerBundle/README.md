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

## Events ##
To add additional actions to the installation process you may use event listeners.
Currently only "onFinish" installer event dispatched.

Example:

``` yaml
services:
    installer.listener.finish.event:
        class:  Acme\Bundle\MyBundle\EventListener\MyListener
        tags:
            - { name: kernel.event_listener, event: installer.finish, method: onFinish }
```

``` php
<?php

namespace Acme\Bundle\MyBundle\EventListener;

class MyListener
{
    public function onFinish()
    {
        // do something
    }
}

```

## Sample data ##
To provide demo fixtures for your bundle just place them in "YourBundle\DataFixtures\Demo" directory.