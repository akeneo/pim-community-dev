Address Validators
------------------

OroAddressBundle has specific validators that can be used to validate addresses and address collection.

### Classes Description

* **Validator \ Contraints \ ContainsPrimaryValidator** - checks that address collection always contains only one primary address;
* **Validator \ Contraints \ ContainsPrimary** - contains error message for ContainsPrimaryValidator;
* **Validator \ Contraints \ UniqueAddressTypesValidator** - checks that address collection has no more than one address for each address type;
* **Validator \ Contraints \ UniqueAddressTypes** - contains error message for UniqueAddressTypesValidator.

### Example Of Usage

Validation configuration should be placed in file Resources/config/validation.yml in appropriate bundle.

```
OroCRM\Bundle\ContactBundle\Entity\Contact:
    properties:
        addresses:
            - Oro\Bundle\AddressBundle\Validator\Constraints\UniqueAddressTypes: ~
```
