# Add a new attribute type

## Context

This documentation will help you to add a new attribute type in the PIM. 
We will add a new attribute type called `Range`, defined by a min and a max value.
This documentation will allow you to:
- be able to save product (and product model) values with this attribute type,
- index these values in the search engine
- calculate the completeness of these entities,
- update the values in the product edit form,
- export your product values.

This documentation will not cover:
- the search on the values defined on this attribute (you can take a look at `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface`),
- the import processes (take a look at `Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface`).

## Step 1: Create the attribute type (backend)

You need to create a new `AttributeTypeInterface` and add its service. 
To do so, you can use the `AbstractAttributeType` and add its definition as a service.

```php
<?php #src/Acme/RangeBundle/AttributeType/RangeType.php

namespace Acme\RangeBundle\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;

class RangeType extends AbstractAttributeType
{
    const RANGE = 'range';

    public function getName()
    {
        return self::RANGE;
    }
}
```

Add this class in `services.yml` with the `pim_catalog.attribute_type` tag.
Please note that services with such a tag will be loaded by the `RegisterAttributePass`.
You can customize the backend type for special validation and normalization processes.
We will use the default one `text`. 

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml
services:
    acme.range.attributetype.range:
        class: Acme\RangeBundle\AttributeType\RangeType
        arguments: ['text']
        tags:
            - { name: pim_catalog.attribute_type, alias: range, entity: '%pim_catalog.entity.product.class%' }
```

## Step 2: Create the values

For the sake of simplicity, we will re-use the same format both for back-end and front-end.
A range value will be defined as an array, with `min` and `max` as keys.
The factories are here to create localizable and scopable values, linked to a specific attribute.

```php
<?php # src/Acme/RangeBundle/Product/Value/RangeValue.php

namespace Acme\RangeBundle\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class RangeValue extends AbstractValue
{
    public function isEqual(ValueInterface $value) : bool
    {
        return $value instanceof RangeValue && $value->getData() === $this->getData();
    }

    public function getData()
    {
        return $this->data;
    }

    public function __toString() : string
    {
        return sprintf('[%s...%s]', $this->data['min'] ?? '', $this->data['max'] ?? '');
    }
}
```

```php
<?php # src/Acme/RangeBundle/Product/Factory/Value/RangeValueFactory.php

namespace Acme\RangeBundle\Product\Factory\Value;

use Acme\RangeBundle\AttributeType\RangeType;
use Acme\RangeBundle\Product\Value\RangeValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class RangeValueFactory implements ValueFactory
{
    public function createByCheckingData(
        Attribute $attribute,
        ?string $channelCode,
        ?string $localeCode, 
        $data
    ): ValueInterface {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        if (!isset($data['min'])) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->code(),
                'min',
                static::class,
                $data
            );
        }
        
        if (null === $data['min'] && null === $data['max']) {
            throw InvalidPropertyException::valueNotEmptyExpected(
                $attribute->code(),
                static::class
            );
        }

        if (!isset($data['max'])) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->code(),
                'max',
                static::class,
                $data
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if ($attribute->isLocalizableAndScopable()) {
            return RangeValue::scopableLocalizableValue($attribute->code(), $data, $channelCode, $localeCode);
        }
        if ($attribute->isScopable()) {
            return RangeValue::scopableValue($attribute->code(), $data, $channelCode);
        }
        if ($attribute->isLocalizable()) {
            return RangeValue::localizableValue($attribute->code(), $data, $localeCode);
        }

        return RangeValue::value($attribute->code(), $data);
    }

    public function supportedAttributeType(): string
    {
        return RangeType::RANGE;
    }
}
```

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    acme.range.factory.value.range:
        class: 'Acme\RangeBundle\Product\Factory\Value\RangeValueFactory'
        tags: ['akeneo.pim.enrichment.factory.product_value']
```

## Step 3: Process the values

You will need 2 new services to process the values: a setter and a comparator.
First, the setter, used by the product updater, responsible for updating entity values.
For this one, as there is no specific processing, we will use the default `AttributeSetter`:

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    acme.range.updater.setter.range_value:
        class: '%pim_catalog.updater.setter.value.class%'
        parent: pim_catalog.updater.setter.abstract
        arguments: [['range']]
        tags:
            - { name: 'pim_catalog.updater.setter' }
```

Next, the comparator, used to know whether the value was updated or not. A comparator returns null if the data was not updated.

```php
<?php #src/Acme/RangeBundle/Product/Comparator/Attribute/RangeComparator.php

namespace Acme\RangeBundle\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

class RangeComparator implements ComparatorInterface
{
    public function supports($data)
    {
        return $data === 'range';
    }

    public function compare($data, $originals)
    {
        $original = $originals['data'];
        $new = $data['data'];

        if (isset($new['min']) && isset($new['max']) && 
            isset($original['min']) && isset($original['max']) &&
            $new['min'] === $original['min'] &&
            $new['max'] === $original['max']
        ) {
            return null;
        }

        return $data;
    }
}
```

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    acme.range.comparator.attribute.range:
        class: Acme\RangeBundle\Product\Comparator\Attribute\RangeComparator
        tags:
            - { name: pim_catalog.attribute.comparator }
```

## Step 4: Indexing the values

You'll now want to properly index the values in the search engine (Elasticsearch), at the following format:
```json
{  
  "values": {
    "my_range_attribute-range": {
      "<all_channels>": {
        "<all_locales>": {
          "min": 0,
          "max": 50
        }
      }
    }
  } 
}
```

For this you will need to create the adequate normalizer:

```php
<?php #src/Acme/RangeBundle/Product/Normalizer/Indexing/Value/RangeValueNormalizer.php

namespace Acme\RangeBundle\Product\Normalizer\Indexing\Value;

use Acme\RangeBundle\AttributeType\RangeType;
use Acme\RangeBundle\Product\Value\RangeValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

class RangeValueNormalizer extends AbstractProductValueNormalizer implements CacheableSupportsMethodInterface 
{
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RangeValue;
    }
    
    protected function getNormalizedData(ValueInterface $value)
    {
        return $value->getData();
    }
    
    public function hasCacheableSupportsMethod() : bool
    {
        return true;
    }
}
```

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    Acme\RangeBundle\Product\Normalizer\Indexing\Value\RangeValueValidator:        
        arguments:
            - '@akeneo.pim.structure.query.get_attributes'
        tags:
            - { name: pim_indexing_serializer.normalizer, priority: 90 }
```

At some point, you'll probably want to perform searches on these values.

**Warning:** this documentation does not describe the implementation of the filters (neither on backend nor frontend side)

In order to do this, you'll have to tell Elasticsearch how to manage your values,
(here we want min and max considered as numbers), by registering new dynamic templates:

```yaml
# src/Acme/RangeBundle/Resources/elasticsearch/product_mapping.yml

mappings:
    dynamic_templates:
        -
            range_min:
                path_match: 'values.*-range.*.min'
                mapping:
                    type: 'double'
        -
            range_max:
                path_match: 'values.*-range.*.max'
                mapping:
                    type: 'double'
```

All you have to do now is include this file in the elasticsearch configuration:

```yaml
# src/Acme/RangeBundle/Resources/config/parameters.yml
parameters:
    elasticsearch_index_configuration_files:
        - '%pim_ce_dev_src_folder_location%/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/settings.yml'
        - '%pim_ce_dev_src_folder_location%/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/product_mapping.yml'
        - '%kernel.project_dir%/src/Acme/RangeBundle/Resources/elasticsearch/product_mapping.yml'
```

## Step 5: Completeness

To be able to compute the completeness, a value has to be defined as **complete** or **incomplete**.
Since Akeneo PIM 4.0, we use the notion of masks to generate keys for each filled value.
The generated mask for a filled value should look like `attributeCode-channelCode-localeCode`.
For more information about completeness masks, please read the documentation of `RequiredAttributesMask` class.
You need to define your own mask generator for your new attribute type.
In our case, we will define a value as **complete** only if the `min` and `max` are filled.
You can define your own logic in this class.

```php
<?php #src/Acme/RangeBundle/Product/Completeness/MaskItemGenerator/RangeMaskItemGenerator.php

namespace Acme\RangeBundle\Product\Completeness\MaskItemGenerator;

use Acme\RangeBundle\AttributeType\RangeType;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;

class RangeMaskItemGenerator implements MaskItemGeneratorForAttributeType
{
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        if ($value['min'] !== null && $value['min'] !== '' && $value['max'] !== null && $value['max'] !== '') {
            return [
                sprintf(
                    '%s-%s-%s',
                    $attributeCode,
                    $channelCode,
                    $localeCode
                )
            ];
        } else {
            return [];
        }
    }

    public function supportedAttributeTypes(): array
    {
        return [RangeType::RANGE];
    }
}
```

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    acme.range.completeness.mask_item_generator.range:
        class: Acme\RangeBundle\Product\Completeness\MaskItemGenerator\RangeMaskItemGenerator
        tags: [{ name: akeneo.pim.enrichment.completeness.mask_item_generator }]
```

## Step 6: User Interface

You will need to display a new field in the product edit form to be able to use your new attribute type.
Add a new HTML template with 2 fields. 
You can customize the style of your page using our [styleguide](https://docs.akeneo.com/master/design_pim/styleguide/index.php). 

```html
<!-- src/Acme/RangeBundle/Resources/public/templates/product/field/range-field.html -->
<input class="AknTextField AknTextField--noRightRadius min" value="<%- value.data.min %>">
<input class="AknTextField AknTextField--noLeftRadius max" value="<%- value.data.max %>">
```

Register it in the `requirejs` file:
```yaml
# src/Acme/RangeBundle/Resources/config/requirejs.yml
config:
    paths:
        acme/template/range-field: acmerange/templates/product/field/range-field.html
```

Each attribute type (text, boolean, single option, etc) have a dedicated javascript module loaded in the product edit form.
You have to create a new `range` javascript module to edit these values. 
The `updateModel` function will be called each time the user fills in the fields.  

```javascript
// src/Acme/RangeBundle/Resources/public/js/product/field/range-field.js
define(
    ['underscore', 'pim/field', 'acme/template/range-field'],
    (_, Field, template) => {
        return Field.extend({
            inputTemplate: _.template(template),
            events: { 'change input': 'updateModel' },

            renderInput(templateContext) {
                return this.inputTemplate(_.extend(templateContext));
            },

            updateModel: function () {
                this.setCurrentValue({
                    min: this.$('.min').val(),
                    max: this.$('.max').val()
                });
            }
        });
    }
);
```

Register it as a new entry in your `requirejs.yml` file:
```yaml
# src/Acme/RangeBundle/Resources/config/requirejs.yml
config:
    paths:
        acme/range-field: acmerange/js/product/field/range-field
        acme/template/range-field: acmerange/templates/product/field/range-field.html
```

Next, the backend needs to indicate to the front what module to use regarding the attribute type.
To that end, each attribute type has a corresponding `FieldProviderInterface`.
Define this new provider, it only supports `range` attribute types.
This class indicates what javascript extension to use (here, `acme-range-field`).

```php
<?php #src/Acme/RangeBundle/Enrich/Provider/Field/RangeFieldProvider.php

namespace Acme\RangeBundle\Enrich\Provider\Field;

use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Acme\RangeBundle\AttributeType\RangeType;

class RangeFieldProvider implements FieldProviderInterface
{
    public function getField($element)
    {
        return 'acme-range-field';
    }

    public function supports($element)
    {
        return $element instanceof AttributeInterface && $element->getType() === RangeType::RANGE;
    }
}
```

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    acme.range.provider.field.range:
        class: Acme\RangeBundle\Enrich\Provider\Field\RangeFieldProvider
        tags:
            - { name: pim_enrich.provider.field }
```

Then, register in the form extensions, that the `acme-range-field` has to load the `acme/range-field` extension defined above. 
```
# src/Acme/RangeBundle/Resources/config/form_extensions.yml
attribute_fields:
    acme-range-field: acme/range-field
```

Finally, you can add the label and icon of this new range attribute by setting a translation and a CSS class:

```yaml
#src/Acme/RangeBundle/Resources/translations/jsmessages.en_US.yml
pim_enrich.entity.attribute.property.type.range: 'Range'
```

```less
// src/Acme/RangeBundle/Resources/public/less/index.less
@import (less) "./public/bundles/acmerange/less/Button.less";
```

```less
// src/Acme/RangeBundle/Resources/public/less/Button.less
.AknButton {
  &-squareIcon {
    &--range {
      background-image: url("/bundles/pimui/images/attribute/icon-metric.svg");
    }
  }
}
```

## Step 7: Export values

If you want to be able to export values through flat files, you need to code how to convert a range value into a cell.
Here, a value will be normalized like this: `[10...20]`.

```php
<?php #src/Acme/RangeBundle/Product/Connector/ArrayConverter/StandardToFlat/Product/ValueConverter/RangeConverter.php

namespace Acme\RangeBundle\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;

class RangeConverter extends AbstractValueConverter implements ValueConverterInterface
{
    public function convert($attributeCode, $data)
    {
        $convertedItem = [];

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );

            $convertedItem[$flatName] = sprintf('[%d...%d]', $value['data']['min'], $value['data']['max']);
        }

        return $convertedItem;
    }
}
```

```yaml
# src/Acme/RangeBundle/Resources/config/services.yml [...]
    acme.array_converter.standard_to_flat.product.value_converter.range:
        class: 'Acme\RangeBundle\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\RangeConverter'
        arguments:
            - '@pim_connector.array_converter.flat_to_standard.product.attribute_columns_resolver'
            - ['range']
        tags:
            - { name: 'pim_connector.array_converter.standard_to_flat.product.value_converter' }
```
