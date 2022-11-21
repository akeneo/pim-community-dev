<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TransformRawValuesCollections
{
    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    public function toValueCollectionsIndexedByType(array $rawValueCollections): array
    {
        $attributes = $this->getAttributesUsedByProducts($rawValueCollections);

        if (empty($attributes)) {
            return [];
        }

        $typesToValues = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach ($rawValues as $attributeCode => $values) {
                if (isset($attributes[$attributeCode])) {
                    $type = $attributes[$attributeCode]->type();
                    $properties = $attributes[$attributeCode]->properties();

                    $typesToValues[$type][$attributeCode][] = [
                        'identifier' => $productIdentifier,
                        'values' => $values,
                        'properties' => $properties
                    ];
                }
            }
        }

        return $typesToValues;
    }

    private function getAttributesUsedByProducts(array $rawValueCollections): array
    {
        $attributeCodes = [];

        foreach ($rawValueCollections as $productIdentifier => $rawValues) {
            foreach (array_keys($rawValues) as $attributeCode) {
                $attributeCodes[] = (string) $attributeCode;
            }
        }

        $attributes = $this->getAttributes->forCodes(array_values(array_unique($attributeCodes)));

        return $attributes;
    }
}
