# Extend the format of products and product models in the search engine

As of v4.0, the indexation of a product in Elasticsearch is now
performed via a single entrypoint: the
`Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection`.
Its implementation is quite naive: a constructor which sets all of its
properties, and a `toArray()` method called by the ProductIndexer in
order to build the document that will be sent to Elasticsearch. The same
logic is applied to the indexation of product models, with
`Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection`.

These classes are `final`, meaning we do not mean them to be extended
nor overridden. However, you might want to add new properties to the
elasticsearch documents, in order to perform custom searches. Here is
how you can achieve it:

## Append a property to the product projection

Let's imagine you want to add an `external_id` property to the product
projection. All you need to do is implement the
`Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface`
interface:

```php
<?php

declare(strict_types=1);

namespace Acme\Bundle\MyCustomBundle\Elasticsearch\Product;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

class AddExternalId implements GetAdditionalPropertiesForProductProjectionInterface
{
    public function fromProductIdentifiers(array $productIdentifiers) : array
    {
        $additionalProperties = [];
        foreach ($productIdentifiers as $productIdentifier) {
            $additionalProperties[$productIdentifier] = ['external_id' => $this->getExternalId($productIdentifier)];
        }
        
        return $additionalProperties;
    }
    
    private function getExternalId(string $productIdentifier): string
    {
        // ...your logic here
    }
}
```

Now you need to register the service, and tag it as `akeneo.pim.enrichment.product.query.indexing_additional_properties`:

```yaml
# src/Acme/Bundle/MyCustomBundle/Resources/config/services.yml

services:
    Acme\Bundle\MyCustomBundle\Elasticsearch\Product\AddExternalId:
        arguments:
            - ...
        tags:
            - { name: akeneo.pim.enrichment.product.query.indexing_additional_properties }
```

## Append a property to the product model projection

It's the exact same logic, except you'll need to implement
`Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface`:

```php
<?php

declare(strict_types=1);

namespace Acme\Bundle\MyCustomBundle\Elasticsearch\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductModelProjectionInterface;

class AddExternalId implements GetAdditionalPropertiesForProductModelProjectionInterface
{
    public function fromProductModelCodes(array $productModelCodes) : array
    {
        $additionalProperties = [];
        foreach ($productModelCodes as $productModelCode) {
            $additionalProperties[$productModelCode] = [
                'external_id' => $this->getExternalId($productModelCode),
            ];
        }
        
        return $additionalProperties;
    }
    
    private function getExternalId(string $productModelCode): string
    {
        // ...your logic here
    }
}
```

and tag it as `akeneo.pim.enrichment.product_model.query.indexing_additional_properties`

```yaml
# src/Acme/Bundle/MyCustomBundle/Resources/config/services.yml

services:
    [...]

    Acme\Bundle\MyCustomBundle\Elasticsearch\ProductModel\AddExternalId:
        arguments:
            - ...
        tags:
            - { name: akeneo.pim.enrichment.product_model.query.indexing_additional_properties }
```

## Further considerations

- the `fromProductIdentifiers()` and `fromProductModelCodes()` methods
  are expected to return an array of arrays, indexed by product
  identifier or product model code, under the following form:

```php
  [
    'product_identifier_1' => [
        'property_1' => $valueToIndex1,
        'property_2' => $valueToIndex2,
        //...
    ],
    'product_identifier_2' => [
        'property_1' => $otherValueToIndex1,
        'property_2' => $otherValueToIndex2,
        //...
    ],
  ]
```
The indexed values must be json encodable, meaning they can be either
scalars, arrays of scalars or objects (implementing the
`\JsonSerializable` interface is a bonus). For more information see
https://www.php.net/manual/en/function.json-encode.php

- You can declare several implementations of
  `GetAdditionalPropertiesForProduct[Model]ProjectionInterface`, all you
  have to do is add the correct tag to their service definitions. If you
  need one of them to be executed first, you can set a priority as long
  with the tag name; services with higher priorities will be injected
  (and executed) first. The default priority is 0.

```yaml
# src/Acme/Bundle/MyCustomBundle/Resources/config/services.yml

services:
    Acme\Bundle\MyCustomBundle\Elasticsearch\ProductModel\AddExternalId:
        tags:
            - { name: akeneo.pim.enrichment.product_model.query.indexing_additional_properties, priority: 100 }
```

- In case several services would add the same additional property (which
  we strongly advise against), the last one wins (meaning the one with
  the lowest priority).
