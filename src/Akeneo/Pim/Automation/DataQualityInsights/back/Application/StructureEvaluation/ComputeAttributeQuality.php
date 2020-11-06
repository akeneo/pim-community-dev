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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheckCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;

final class ComputeAttributeQuality
{
    public static function computeGlobalQuality(?AttributeSpellcheck $attributeSpellcheck, AttributeOptionSpellcheckCollection $attributeOptionsSpellchecks): Quality
    {
        if (null === $attributeSpellcheck) {
            return Quality::processing();
        }

        if (true === $attributeSpellcheck->isToImprove() || $attributeOptionsSpellchecks->hasAttributeOptionToImprove()) {
            return Quality::toImprove();
        }

        if (false === $attributeSpellcheck->isToImprove() || $attributeOptionsSpellchecks->hasOnlyGoodSpellchecks()) {
            return Quality::good();
        }

        return Quality::notApplicable();
    }

    public static function computeLocaleQuality(LocaleCode  $locale, ?AttributeSpellcheck $attributeSpellcheck, AttributeOptionSpellcheckCollection $attributeOptionsSpellchecks): Quality
    {
        if (null === $attributeSpellcheck) {
            return Quality::processing();
        }

        $attributeSpellcheckResult = $attributeSpellcheck->getResult()->getLocaleResult($locale);

        if ((null !== $attributeSpellcheckResult && $attributeSpellcheckResult->isToImprove())
            || $attributeOptionsSpellchecks->hasAttributeOptionToImproveForLocale($locale)
        ) {
            return Quality::toImprove();
        }

        if (null === $attributeSpellcheckResult && $attributeOptionsSpellchecks->isEmptyForLocale($locale)) {
            return Quality::notApplicable();
        }

        return Quality::good();
    }
}
