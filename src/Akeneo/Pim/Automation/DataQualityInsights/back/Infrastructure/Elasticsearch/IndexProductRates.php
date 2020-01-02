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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAxesRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class IndexProductRates
{
    /** @var Client */
    private $esClient;

    /** @var GetProductAxesRates */
    private $getProductAxesRates;

    public function __construct(Client $esClient, GetProductAxesRates $getProductAxesRates)
    {
        $this->esClient = $esClient;
        $this->getProductAxesRates = $getProductAxesRates;
    }

    public function execute(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $productAxesRates = $this->getProductAxesRates->get(new ProductId($productId));
            $formattedRates = $this->formatProductAxesRatesForIndexation($productAxesRates);
            if (! empty($formattedRates)) {
                $this->indexProductRates($productId, $formattedRates);
            }
        }
    }

    private function formatProductAxesRatesForIndexation(array $productAxesRates)
    {
        $formattedRates = [];
        foreach ($productAxesRates as $axisName => $rates) {
            if (! empty($rates['rates'])) {
                $formattedRates[$axisName] = $rates['rates'];
            }
        }

        return $formattedRates;
    }

    private function indexProductRates(int $productId, array $formattedRates): void
    {
        $this->esClient->updateByQuery(
            [
                'script' => [
                    'source' => "ctx._source.rates = params",
                    'params' => $formattedRates,
                ],
                'query' => [
                    'term' => [
                        'id' => sprintf('product_%d', $productId),
                    ],
                ],
            ]
        );
    }
}
