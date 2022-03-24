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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\KeyIndicator\ComputeStructureKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

final class PimEnterpriseGetKeyIndicators implements GetKeyIndicatorsInterface
{
    public function __construct(
        private GetKeyIndicatorsInterface $getProductsAndProductModelsKeyIndicators,
        private ComputeStructureKeyIndicator $computeAttributesPerfectSpelling
    ) {
    }

    public function all(ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        return $this->mergeProductsAndStructureKeyIndicators(
            $this->getProductsAndProductModelsKeyIndicators->all($channelCode, $localeCode),
            $this->computeAttributesPerfectSpelling->computeByLocale($localeCode)
        );
    }

    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family): array
    {
        return $this->mergeProductsAndStructureKeyIndicators(
            $this->getProductsAndProductModelsKeyIndicators->byFamily($channelCode, $localeCode, $family),
            $this->computeAttributesPerfectSpelling->computeByLocaleAndFamily($localeCode, $family)
        );
    }

    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category): array
    {
        return $this->mergeProductsAndStructureKeyIndicators(
            $this->getProductsAndProductModelsKeyIndicators->byCategory($channelCode, $localeCode, $category),
            $this->computeAttributesPerfectSpelling->computeByLocaleAndCategory($localeCode, $category)
        );
    }

    private function mergeProductsAndStructureKeyIndicators(array $productsAndProductModelsKeyIndicators, KeyIndicator $structureKeyIndicator): array
    {
        return array_merge($productsAndProductModelsKeyIndicators, [strval($structureKeyIndicator->getCode()) => $structureKeyIndicator->toArray()]);
    }
}
