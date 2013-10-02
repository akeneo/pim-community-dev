EmailsBundle
==============

This bundle provides email related functionality.

Installation
------------

### Step 1) Get the bundle and the library

Add on composer.json (see http://getcomposer.org/)

    "require" :  {
        // ...
        "oro/email-bundle": "dev-master",
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
        new Oro\Bundle\EmailBundle\OroEmailBundle(),
    );
    // ...
}
```

Usage:
------

 - [Emails](./Resources/doc/emails.md)
 - [Email templates](./Resources/doc/email_templates.md)
