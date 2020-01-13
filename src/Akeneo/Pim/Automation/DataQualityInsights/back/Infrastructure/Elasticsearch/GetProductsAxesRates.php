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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * Format example (product id as key):
 *  [
 *      1207 => [
 *         "enrichment" => [
 *             "mobile" => [
 *                 "de_DE" => "E",
 *                 "en_US" => "E",
 *                 "fr_FR" => "E"
 *             ],
 *             "ecommerce" => [
 *                 "de_DE" => "E",
 *                 "en_US" => "E",
 *                 "fr_FR" => "E"
 *             ]
 *         ],
 *         "consistency" => [
 *             "mobile" => [
 *                 "de_DE" => "A",
 *                 "en_US" => "A",
 *                 "fr_FR" => "A"
 *             ],
 *             "ecommerce" => [
 *                 "de_DE" => "A",
 *                 "en_US" => "A",
 *                 "fr_FR" => "A"
 *             ]
 *         ]
 *      ],
 *      1542 => []
 *  ]
 *
 */
class GetProductsAxesRates
{
    /** @var GetLatestProductAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    public function __construct(GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
    }

    public function fromProductIds(array $productIds): array
    {
        $productsRates = [];
        $rawProductRates = $this->getLatestProductAxesRatesQuery->byProductIds($productIds);
        foreach ($rawProductRates as $productId => $axisChannelLocaleRates) {
            $productRates = [];
            foreach ($axisChannelLocaleRates as $axis => $channelLocalesRates) {
                $productRates[$axis] = [];
                foreach ($channelLocalesRates as $channel => $localeRates) {
                    $productRates[$axis][$channel] = array_map(function ($rawRate) {
                        $rate = new Rate(intval($rawRate['rate']));
                        return strval($rate);
                    }, $localeRates);
                }
            }
            $productsRates[$productId] = $productRates;
        }

        return $productsRates;
    }
}
