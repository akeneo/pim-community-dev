Address Type
------------

Address type is the entity that is used to specify type of address. Address can have several address types.
Be default there are two address types - billing and shipping. Address type entity called AddressType
and stored in Oro/Bundle/AddressBundle/Entity/AddressType.php. It has two properties:
"name" that defined symbolic name of type and "label" that is used at frontend.
Address types are translatable entities - their label should be defined for each supported locale.
Loading and translation of address types performed in data fixture
Oro/Bundle/AddressBundle/DataFixtures/ORM/LoadAddressTypeData.php.

There is abstract address entity that support types - AbstractTypedAddress.
It has property "types" and methods to work with it, but DB relation between address and address type
must be defined in specific class:

``` php
/**
 * @var Collection
 *
 * @ORM\ManyToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType")
 * @ORM\JoinTable(
 *     name="orocrm_contact_address_to_address_type",
 *     joinColumns={@ORM\JoinColumn(name="contact_address_id", referencedColumnName="id")},
 *     inverseJoinColumns={@ORM\JoinColumn(name="type_name", referencedColumnName="name")}
 * )
 **/
protected $types;
```
