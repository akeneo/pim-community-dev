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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;

final class GetProductsKeyIndicators
{
    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    /** @var GetProductsKeyIndicator[] */
    private iterable $keyIndicatorQueries;

    public function __construct(GetLocalesByChannelQueryInterface $getLocalesByChannelQuery, iterable $keyIndicatorQueries)
    {
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
        $this->keyIndicatorQueries = $keyIndicatorQueries;
    }

    public function get(array $productIds)
    {
        $localesByChannel = $this->getLocalesByChannelQuery->getArray();
        $keyIndicatorsResultsByName = $this->executeAllKeyIndicatorsQueries($productIds);

        $productsKeyIndicators = [];
        foreach ($productIds as $productId) {
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

    private function executeAllKeyIndicatorsQueries(array $productIds): array
    {
        $keyIndicatorsResults = [];
        foreach ($this->keyIndicatorQueries as $keyIndicatorQuery) {
            $keyIndicatorResult = $keyIndicatorQuery->execute($productIds);
            if (! empty($keyIndicatorResult)) {
                $keyIndicatorsResults[$keyIndicatorQuery->getName()] = $keyIndicatorResult;
            }
        }

        return $keyIndicatorsResults;
    }
}
