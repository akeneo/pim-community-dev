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

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;

class RemoveUniqueAttributeValues
{
    public static function fromCollection(WriteValueCollection $valueCollection, array $uniqueAttributeCodes): void
    {
        $attributeCodes = $valueCollection->getAttributeCodes();
        foreach ($attributeCodes as $attributeCode) {
            if(in_array($attributeCode, $uniqueAttributeCodes)) {
                $valueCollection->removeByAttributeCode($attributeCode);
            }
        }
    }
}
