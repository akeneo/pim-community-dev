<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeProductsKeyIndicators
{
    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    /** @var ComputeProductsKeyIndicator[] */
    private iterable $keyIndicatorQueries;

    public function __construct(GetLocalesByChannelQueryInterface $getLocalesByChannelQuery, iterable $keyIndicatorQueries)
    {
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
        $this->keyIndicatorQueries = $keyIndicatorQueries;
    }

    public function compute(ProductEntityIdCollection $productIdCollection): array
    {
        $localesByChannel = $this->getLocalesByChannelQuery->getArray();
        $keyIndicatorsResultsByName = $this->executeAllKeyIndicatorsQueries($productIdCollection);

        $productsKeyIndicators = [];
        foreach ($productIdCollection as $productId) {
            $productId = (string) $productId;
            foreach ($localesByChannel as $channel => $locales) {
                foreach ($locales as $locale) {
                    foreach ($keyIndicatorsResultsByName as $keyIndicatorName => $keyIndicatorResultsByProduct) {
                        $productsKeyIndicators[$productId][$channel][$locale][$keyIndicatorName] = $keyIndicatorResultsByProduct[$productId][$channel][$locale] ?? null;
                    }
                }
            }
        }

        return $productsKeyIndicators;
    }

    private function executeAllKeyIndicatorsQueries(ProductEntityIdCollection $productIdCollection): array
    {
        $keyIndicatorsResults = [];
        foreach ($this->keyIndicatorQueries as $keyIndicatorQuery) {
            $keyIndicatorResult = $keyIndicatorQuery->compute($productIdCollection);
            if (!empty($keyIndicatorResult)) {
                $keyIndicatorsResults[(string)$keyIndicatorQuery->getCode()] = $keyIndicatorResult;
            }
        }

        return $keyIndicatorsResults;
    }
}
