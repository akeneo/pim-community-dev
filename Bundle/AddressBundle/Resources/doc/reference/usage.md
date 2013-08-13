Usage
-----

OroAddressBundle provides PHP/REST/SOAP API for address CRUD operations.

### PHP API

``` php
<?php
    //Accessing address manager from controller
    /** @var  $addressManager \Oro\Bundle\AddressBundle\Entity\Manager\AddressManager */
    $addressManager = $this->get('oro_address.address.provider')->getStorage();

    //create empty address entity
    $address = $addressManager->createAddress();

    //process insert/update
    $this->get('oro_address.form.handler.address')->process($entity)

    //accessing address form service
    $this->get('oro_address.form.address')
```

### REST API

<pre>
    oro_api_get_addresses     GET           /api/rest/{version}/addresses.{_format}
    oro_api_get_address       GET           /api/rest/{version}/addresses/{id}.{_format}
    oro_api_post_address      POST          /api/rest/{version}/address.{_format}
    oro_api_put_address       PUT           /api/rest/{version}/address.{_format}
    oro_api_delete_address    DELETE        /api/rest/{version}/address.{_format}
    oro_api_get_addresstype   GET           /api/rest/{version}/addresstypes/{name}.{_format}
    oro_api_get_addresstypes  GET           /api/rest/{version}/addresstypes.{_format}
</pre>

### Address collection
Address collection may be added to form with next three steps
1) Add field with type oro_address_collection to form

```php
$builder->add(
    'addresses',
    'oro_address_collection',
    array(
        'required' => false,
        'type'     => 'oro_address'
    )
);
```
2) Add AddressCollectionTypeSubscriber. AddressCollectionTypeSubscriber must be initialized with address collection field name and address class name.

```php
$builder->addEventSubscriber(new AddressCollectionTypeSubscriber('addresses', $this->addressClass));
```

3) In template add OroAddressBundle:Include:fields.html.twig to support address form field typed

```php
{% form_theme form with ['OroAddressBundle:Include:fields.html.twig']}
```
