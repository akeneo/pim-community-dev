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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;

class ComputeAttributeQuality
{
    /** @var GetAttributeSpellcheckQueryInterface */
    private $getAttributeSpellcheckQuery;

    /** @var GetAttributeOptionSpellcheckQueryInterface */
    private $getAttributeOptionSpellcheckQuery;

    public function __construct(
        GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery
    ) {
        $this->getAttributeSpellcheckQuery = $getAttributeSpellcheckQuery;
        $this->getAttributeOptionSpellcheckQuery = $getAttributeOptionSpellcheckQuery;
    }

    public function byAttributeCode(AttributeCode $attributeCode): Quality
    {
        $attributeSpellcheck = $this->getAttributeSpellcheckQuery->getByAttributeCode($attributeCode);

        if (null === $attributeSpellcheck) {
            return Quality::processing();
        }

        if ($attributeSpellcheck->isToImprove()) {
            return Quality::toImprove();
        }

        $attributeOptionsSpellchecks = $this->getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode);

        if ($attributeOptionsSpellchecks->isEmpty()) {
            return false === $attributeSpellcheck->isToImprove() ? Quality::good() : Quality::notApplicable();
        }

        if ($attributeOptionsSpellchecks->hasAttributeOptionToImprove()) {
            return Quality::toImprove();
        }

        if (false === $attributeSpellcheck->isToImprove() || $attributeOptionsSpellchecks->hasOnlyGoodSpellchecks()) {
            return Quality::good();
        }

        return Quality::notApplicable();
    }
}
