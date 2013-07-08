Installation
------------

### Step 1) Get the bundle and the library

Add on composer.json (see http://getcomposer.org/)

    "require" :  {
        // ...
        "oro/address-bundle": "dev-master",
    }

### Step 2) Register the bundle

To start using the bundle, register it in your Kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\AddressBundle\OroAddressBundle(),
    );
    // ...
}
```

### Dependencies

* Doctrine ORM, https://github.com/doctrine/doctrine2
* DoctrineBundle, https://github.com/doctrine/DoctrineBundle
* FOSRestBundle https://github.com/FriendsOfSymfony/FOSRestBundle
* FOSJsRoutingBundle https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
* BeSimpleSoapBundle, https://github.com/BeSimple/BeSimpleSoapBundle
* NelmioApiDocBundle https://github.com/nelmio/NelmioApiDocBundle
* OroFlexibleEntityBundle, https://github.com/orocrm/platform/tree/master/src/Oro/Bundle/FlexibleEntityBundle
* OroFormBundle, https://github.com/orocrm/platform/tree/master/src/Oro/Bundle/FormBundle
* OroTranslationBundle, https://github.com/orocrm/platform/tree/master/src/Oro/Bundle/TranslationBundle
