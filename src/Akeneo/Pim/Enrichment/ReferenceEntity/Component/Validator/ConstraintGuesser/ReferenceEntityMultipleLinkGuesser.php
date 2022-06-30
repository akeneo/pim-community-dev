<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\DuplicateRecords;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class ReferenceEntityMultipleLinkGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::REFERENCE_ENTITY_COLLECTION,
            ]
        );
    }

    public function guessConstraints(AttributeInterface $attribute)
    {
        return [
            new DuplicateRecords(['attributeCode' => $attribute->getCode()]),
        ];
    }
}
