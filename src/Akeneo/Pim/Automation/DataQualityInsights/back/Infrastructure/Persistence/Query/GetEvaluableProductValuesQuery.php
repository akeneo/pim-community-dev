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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetEvaluableAttributesByProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductRawValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

class GetEvaluableProductValuesQuery implements GetEvaluableProductValuesQueryInterface
{
    /** @var GetProductRawValuesQueryInterface */
    private $getProductRawValuesQuery;

    /** @var GetEvaluableAttributesByProductQueryInterface */
    private $getEvaluableAttributesByProductQuery;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    public function __construct(
        GetProductRawValuesQueryInterface $getProductRawValuesQuery,
        GetEvaluableAttributesByProductQueryInterface $getEvaluableAttributesByProductQuery,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $this->getProductRawValuesQuery = $getProductRawValuesQuery;
        $this->getEvaluableAttributesByProductQuery = $getEvaluableAttributesByProductQuery;
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function byProductId(ProductId $productId): ProductValuesCollection
    {
        $productValuesCollection = new ProductValuesCollection();
        $attributes = $this->getEvaluableAttributesByProductQuery->execute($productId);

        if (empty($attributes)) {
            return $productValuesCollection;
        }

        $channelsLocales = $this->localesByChannelQuery->getChannelLocaleCollection();
        $rawValues = $this->getProductRawValuesQuery->execute($productId);

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $productValues = $this->buildProductValuesByChannelAndLocale($attribute, $channelsLocales, $rawValues);
            if (null !== $productValues) {
                $productValuesCollection->add($productValues);
            }
        }

        return $productValuesCollection;
    }

    private function buildProductValuesByChannelAndLocale(Attribute $attribute, ChannelLocaleCollection $channelsLocales, array $rawValues): ?ProductValues
    {
        $productValues = new ChannelLocaleDataCollection();
        $attributeCode = strval($attribute->getCode());

        foreach ($channelsLocales as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $value = isset($rawValues[$attributeCode]) ? $this->getValue($rawValues[$attributeCode], strval($channelCode), strval($localeCode)) : null;
                if (null !== $value) {
                    $productValues->addToChannelAndLocale($channelCode, $localeCode, $value);
                }
            }
        }

        return $productValues->isEmpty() ? null : new ProductValues($attribute, $productValues);
    }

    private function getValue(array $rawValues, string $channelCode, string $localeCode): ?string
    {
        if (isset($rawValues[$channelCode][$localeCode])) {
            return $rawValues[$channelCode][$localeCode];
        }

        if (isset($rawValues[$channelCode]['<all_locales>'])) {
            return $rawValues[$channelCode]['<all_locales>'];
        }

        if (isset($rawValues['<all_channels>'][$localeCode])) {
            return $rawValues['<all_channels>'][$localeCode];
        }

        return $rawValues['<all_channels>']['<all_locales>'] ?? null;
    }
}
