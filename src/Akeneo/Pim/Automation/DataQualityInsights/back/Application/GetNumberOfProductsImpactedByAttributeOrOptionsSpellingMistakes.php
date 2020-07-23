<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;

final class GetNumberOfProductsImpactedByAttributeOrOptionsSpellingMistakes
{
    private $getAttributeQuery;

    private $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery;

    private $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery;

    public function __construct(
        GetAttributeQueryInterface $getAttributeQuery,
        GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryInterface $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery,
        GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery
    ) {
        $this->getAttributeQuery = $getAttributeQuery;
        $this->getNumberOfProductsImpactedByAttributeSpellingMistakesQuery = $getNumberOfProductsImpactedByAttributeSpellingMistakesQuery;
        $this->getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery = $getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery;
    }

    public function byAttributeCode(AttributeCode $attributeCode): int
    {
        $attribute = $this->getAttributeQuery->byAttributeCode($attributeCode);

        if ($attribute === null) {
            return 0;
        }

        $attributeLabelsErrorNumber = $this->getNumberOfProductsImpactedByAttributeSpellingMistakesQuery->byAttributeCode($attributeCode);
        if ($attributeLabelsErrorNumber > 0 || !$attribute->hasOptions()) {
            return $attributeLabelsErrorNumber;
        }

        return $this->getNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery->byAttribute($attribute);
    }
}
