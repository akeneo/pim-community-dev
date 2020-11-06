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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeLocaleQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;

class ConsolidateAttributeQuality
{
    private GetAllActivatedLocalesQueryInterface $getAllActivatedLocalesQuery;

    private GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery;

    private GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery;

    private AttributeQualityRepositoryInterface $attributeQualityRepository;

    private AttributeLocaleQualityRepositoryInterface $attributeLocaleQualityRepository;

    public function __construct(
        GetAllActivatedLocalesQueryInterface $getAllActivatedLocalesQuery,
        GetAttributeSpellcheckQueryInterface $getAttributeSpellcheckQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery,
        AttributeQualityRepositoryInterface $attributeQualityRepository,
        AttributeLocaleQualityRepositoryInterface $attributeLocaleQualityRepository
    ) {
        $this->getAllActivatedLocalesQuery = $getAllActivatedLocalesQuery;
        $this->getAttributeSpellcheckQuery = $getAttributeSpellcheckQuery;
        $this->getAttributeOptionSpellcheckQuery = $getAttributeOptionSpellcheckQuery;
        $this->attributeQualityRepository = $attributeQualityRepository;
        $this->attributeLocaleQualityRepository = $attributeLocaleQualityRepository;
    }

    public function byAttributeCode(AttributeCode $attributeCode): void
    {
        $locales = $this->getAllActivatedLocalesQuery->execute();
        $attributeSpellcheck = $this->getAttributeSpellcheckQuery->getByAttributeCode($attributeCode);
        $attributeOptionsSpellchecks = $this->getAttributeOptionSpellcheckQuery->getByAttributeCode($attributeCode);

        $attributeQuality = ComputeAttributeQuality::computeGlobalQuality($attributeSpellcheck, $attributeOptionsSpellchecks);
        $this->attributeQualityRepository->save($attributeCode, $attributeQuality);

        foreach ($locales as $locale) {
            $quality = ComputeAttributeQuality::computeLocaleQuality($locale, $attributeSpellcheck, $attributeOptionsSpellchecks);
            $this->attributeLocaleQualityRepository->save($attributeCode, $locale, $quality);
        }
    }
}
