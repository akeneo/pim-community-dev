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
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * Update the Franklin Insights subscription status for a product in ES.
 */
class ProductSubscriptionUpdater
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
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
                'script' => [
                    'inline' => sprintf('ctx._source.franklin_subscription = %s', $isSubscribed ? 'true' : 'false'),
                ],
                'query' => [
                    'term' => [
                        'id' => sprintf('product_%d', $productId->toInt()),
                    ],
                ],
            ]
        );
    }
}
