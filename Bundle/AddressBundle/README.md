AddressBundle
=============

the OroAddressBundle implement basic address storage and country/region storage. It provides PHP/REST/SOAP API for address CRUD operations.

**Basic Docs**

* [Installation](#installation)
* [Usage](#usage)

<a name="installation"></a>

## Installation

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

## Usage

### PHP API

``` php
<?php
    //Accessing address manager from controller
    /** @var  $addressManager \Oro\Bundle\AddressBundle\Entity\Manager\AddressManager */
    $addressManager = $this->get('oro_address.address.provider')->getStorage();

    //create empty address entity
    $address = $addressManager->createFlexible();

    //process insert/update
    $this->get('oro_address.form.handler.address')->process($entity)

    //accessing address form service
    $this->get('oro_address.form.address')
```

### REST API

<pre>
    oro_api_delete_address    DELETE        /api/rest/{version}/address.{_format}
    oro_api_get_address       GET           /api/rest/{version}/addresses/{id}.{_format}
    oro_api_get_addresses     GET           /api/rest/{version}/addresses.{_format}
    oro_api_post_address      POST          /api/rest/{version}/address.{_format}
    oro_api_put_address       PUT           /api/rest/{version}/address.{_format}
</pre>

### Address collection
Address collection may be added to form with next three steps
1) Add field with type oro_address_collection to form

```php
$builder->add(
    'multiAddress',
    'oro_address_collection',
    array(
        'required' => false,
        'label' => ' '
    )
);
```
2) Add AddressCollectionTypeSubscriber. AddressCollectionTypeSubscriber must be initialized with address collection field name.

```php
$builder->addEventSubscriber(new AddressCollectionTypeSubscriber('multiAddress'));
```

3) In template add OroAddressBundle:Include:fields.html.twig to support address form field typed

```php
{% form_theme form with ['OroAddressBundle:Include:fields.html.twig']}
```
