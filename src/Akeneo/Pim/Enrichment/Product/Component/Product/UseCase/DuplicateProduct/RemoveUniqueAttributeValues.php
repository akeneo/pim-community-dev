<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;

class RemoveUniqueAttributeValues
{
    /** @var GetUniqueAttributeCodes */
    private $getUniqueAttributeCodes;


    public function __construct(GetUniqueAttributeCodes $getUniqueAttributeCodes)
    {
        $this->getUniqueAttributeCodes = $getUniqueAttributeCodes;
    }

    public function fromProduct(ProductInterface $product): ProductInterface
    {
        $valueCollection = $product->getValues();
        $attributeCodes = $valueCollection->getAttributeCodes();
        $uniqueAttributeCodes = $this->getUniqueAttributeCodes->all();
        foreach ($attributeCodes as $attributeCode) {
            if (in_array($attributeCode, $uniqueAttributeCodes)) {
                $valueCollection->removeByAttributeCode($attributeCode);
            }
        }

        return $product;
    }
}
