<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Value;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCollection
{
    private $valuesIndexedByAttribute = [];

    private function __construct()
    {
    }

    public function valuesIndexedByAttribute(): array
    {
        return $this->valuesIndexedByAttribute;
    }

    public static function fromApiFormat(array $values): ProductValueCollection
    {
        $collection = new self();
        foreach ($values as $attribute => $valuesPerAttribute) {
            foreach ($valuesPerAttribute as $value) {
                $collection->add(new ProductValue($attribute, $value['locale'], $value['scope'], $value['data']));
            }
        }

        return $collection;
    }

    private function add(ProductValue $value)
    {
        $attributeCode = $value->attributeCode();
        $this->valuesIndexedByAttribute[$attributeCode][] = $value;
    }

}
