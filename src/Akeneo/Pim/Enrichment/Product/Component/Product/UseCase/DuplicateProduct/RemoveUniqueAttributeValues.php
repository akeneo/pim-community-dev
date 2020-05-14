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
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;

class RemoveUniqueAttributeValues
{
    /** @var GetUniqueAttributeCodes */
    private $getUniqueAttributeCodes;

    /** @var AttributeRepository */
    private $attributeRepository;

    public function __construct(GetUniqueAttributeCodes $getUniqueAttributeCodes, AttributeRepository $attributeRepository)
    {
        $this->getUniqueAttributeCodes = $getUniqueAttributeCodes;
        $this->attributeRepository = $attributeRepository;
    }

    public function fromCollection(WriteValueCollection $valueCollection): array
    {
        $attributeCodes = $valueCollection->getAttributeCodes();
        $uniqueAttributeCodes = $this->getUniqueAttributeCodes->fromAttributeCodes($attributeCodes);
        foreach ($attributeCodes as $attributeCode) {
            if(in_array($attributeCode, $uniqueAttributeCodes)) {
                $valueCollection->removeByAttributeCode($attributeCode);
            }
        }

        $uniqueAttributeCodesWithoutIdentifier = array_filter(
            $uniqueAttributeCodes,
            function($attributeCode) {
                return $attributeCode !== $this->attributeRepository->getIdentifierCode();
            }
        );

        return $uniqueAttributeCodesWithoutIdentifier;
    }
}
