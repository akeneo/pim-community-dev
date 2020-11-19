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
    private GetKeyIndicatorsInterface $getProductsKeyIndicators;

    private ComputeStructureKeyIndicator $computeAttributesPerfectSpelling;

    public function __construct(GetKeyIndicatorsInterface $getProductsKeyIndicators, ComputeStructureKeyIndicator $computeAttributesPerfectSpelling)
    {
        $this->getProductsKeyIndicators = $getProductsKeyIndicators;
        $this->computeAttributesPerfectSpelling = $computeAttributesPerfectSpelling;
    }

    public function all(ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        return $this->mergeProductsAndStructureKeyIndicators(
            $this->getProductsKeyIndicators->all($channelCode, $localeCode),
            $this->computeAttributesPerfectSpelling->computeByLocale($localeCode)
        );
    }

    public function byFamily(ChannelCode $channelCode, LocaleCode $localeCode, FamilyCode $family): array
    {
        return $this->mergeProductsAndStructureKeyIndicators(
            $this->getProductsKeyIndicators->byFamily($channelCode, $localeCode, $family),
            $this->computeAttributesPerfectSpelling->computeByLocaleAndFamily($localeCode, $family)
        );
    }

    public function byCategory(ChannelCode $channelCode, LocaleCode $localeCode, CategoryCode $category): array
    {
        return $this->mergeProductsAndStructureKeyIndicators(
            $this->getProductsKeyIndicators->byCategory($channelCode, $localeCode, $category),
            $this->computeAttributesPerfectSpelling->computeByLocaleAndCategory($localeCode, $category)
        );
    }

    private function mergeProductsAndStructureKeyIndicators(array $productsKeyIndicators, KeyIndicator $structureKeyIndicator): array
    {
        return $structureKeyIndicator->isEmpty()
            ? $productsKeyIndicators
            : array_merge($productsKeyIndicators, [strval($structureKeyIndicator->getCode()) => $structureKeyIndicator->toArray()])
        ;
    }
}
