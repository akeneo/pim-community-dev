Address Entities
----------------

OroAddressBunle provides several entities to work with addresses.

### Classes Description

* **AbstractAddress** - encapsulates basic address attributes (label, street, city, country, first and last name etc.);
* **AbstractTypedAddress** - extends AbstractAddress and adds flag "primary" and collection of address types;
* **Address** - basic implementation of AbstractAddress;
* **AddressSoap** - extends Address entity to work with SOAP API;
* **Country** - encapsulates country attributes (ISO2 and ISO3 codes, name, collection of regions);
* **CountryTranslation** - translation entity for Country entity;
* **Region** - encapsulates region attributes (combined code "country+region", code, name, country entity) ;
* **RegionTranslation** - translation entity for Region entity;
* **AddressType** - describes address type, includes type name and type label, default types are "billing" and "shipping";
* **AddressTypeTranslation** - translation entity for AddressType entity.
