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

* FOSRestBundle https://github.com/FriendsOfSymfony/FOSRestBundle
* FOSJsRoutingBundle https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
* BeSimpleSoapBundle, https://github.com/BeSimple/BeSimpleSoapBundle
* NelmioApiDocBundle https://github.com/nelmio/NelmioApiDocBundle
* DoctrineBundle, https://github.com/doctrine/DoctrineBundle
* FlexibleEntityBundle, https://github.com/laboro/FlexibleEntityBundle
* Symfony/Intl, https://github.com/symfony/Intl depends on http://php.net/manual/en/book.intl.php ( extension is bundled with PHP as of PHP version 5.3.0 ), that depends on lib-icu (http://site.icu-project.org/)

<a name="usage"></a>
