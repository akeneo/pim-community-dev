<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributeAsMainTitleValueFromProductIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributesByTypeFromProductQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductRawValuesByAttributeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class BuildProductValues implements BuildProductValuesInterface
{
    /** @var GetProductRawValuesByAttributeQueryInterface */
    private $getProductRawValuesByAttributeQuery;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;


    public function __construct(
        GetProductRawValuesByAttributeQueryInterface $getProductRawValuesByAttributeQuery,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $this->getProductRawValuesByAttributeQuery = $getProductRawValuesByAttributeQuery;
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function buildForProductIdAndAttributeCodes(ProductId $productId, array $attributesCodes): array
    {
        $rawValues = $this->getProductRawValuesByAttributeQuery->execute($productId, $attributesCodes);

        return $this->buildByChannelAndLocale($attributesCodes, $rawValues);
    }

    private function buildByChannelAndLocale(array $attributeCodes, array $rawValues): array
    {
        $localesByChannel = $this->localesByChannelQuery->execute();

        $result = [];
        foreach ($attributeCodes as $attributeCode) {
            foreach ($localesByChannel as $channelCode => $localeCodes) {
                foreach ($localeCodes as $localeCode) {
                    $value = null;
                    if (isset($rawValues[$attributeCode])) {
                        $value = $this->getValue($rawValues[$attributeCode], $channelCode, $localeCode);
                    }
                    $result[$attributeCode][$channelCode][$localeCode] = $value;
                }
            }
        }

        return $result;
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
