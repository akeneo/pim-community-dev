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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * Update the Franklin Insights subscription status for a product in ES.
 */
class ProductSubscriptionUpdater
{
    /** @var string */
    private $indexName;

    /** @var Client */
    private $esClient;

    /**
     * @param ClientBuilder $clientBuilder
     * @param array $hosts
     * @param string $indexName
     */
    public function __construct(
        ClientBuilder $clientBuilder,
        array $hosts,
        string $indexName
    ) {
        $this->indexName = $indexName;

        $clientBuilder->setHosts($hosts);
        $this->esClient = $clientBuilder->build();
    }

    public function updateSubscribedProduct(ProductId $productId): void
    {
        $this->updateProduct($productId, true);
    }

    public function updateUnsubscribedProduct(ProductId $productId): void
    {
        $this->updateProduct($productId, false);
    }

    private function updateProduct(ProductId $productId, bool $isSubscribed): void
    {
        $this->esClient->updateByQuery(
            [
                'index' => $this->indexName,
                'type' => 'pim_catalog_product',
                'body' => [
                    'script' => [
                        'inline' => sprintf('ctx._source.franklin_subscription = %s', $isSubscribed ? 'true' : 'false'),
                    ],
                    'query' => [
                        'term' => [
                            'id' => sprintf('product_%d', $productId->toInt()),
                        ],
                    ],
                ],
            ]
        );
    }
}
