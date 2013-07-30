Address Form Types
------------------

OroAddressBundle provides form types to render address entities on forms.

### Form Types Description

* **oro\_address** - encapsulates form fields for Address entity;
* **oro\_address\_value** - form type for flexible entity values fields;
* **address** - form type for API for Address entity;
* **oro\_address\_api\_value** - form type for API for flexible entity values fields;
* **oro\_address\_collection** - collection of form types for address entities;
* **oro\_country** - encapsulates form fields for Country entity;
* **oro\_region** - encapsulates form fields for Region entity.

### Classes Description

* **Form \ Type \ AbstractAddressType** - abstract class for address form type, includes form fields
for address attributes;
* **Form \ Type \ AbstractTypedAddressType** - extends AbstractAddressType, adds functionality
to work with address types;
* **Form \ Type \ AddressType** - implementation of AbstractAddressType, name is "oro_address";
* **Form \ Type \ AddressValueType** - form type for flexible attribute values, name is "oro_address_value";
* **Form \ Type \ AddressApiType** - extends AddressType, used in API, name is "address";
* **Form \ Type \ AddressApiValueType** - form type for API for flexible attribute values,
name is "oro_address_api_value";
* **Form \ Type\ AddressCollectionType** - provides functionality to work with address collections,
name is "oro_address_collection";
* **Form \ Type \ CountryType** - form type for Country entity, name is "oro_country";
* **Form \ Type \ RegionType** - form type fot Region entity, name is "oro_region";
* **Form \ EventListener \ BuildAddressFormListener** - responsible for processing relation
between countries and regions on address form;
* **Form \ EventListener \ AddressCollectionTypeSubscriber** - responsible for processing
of address elements at address collection form;
* **Form \ Handler \ AddressHandler** - processes save for AbstractAddress entity using specified form.
