<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\ProductSubscriptionUpdater;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;

class ProductSubscriptionUpdaterSpec extends ObjectBehavior
{
    public function let(Client $esClient): void
    {
        $this->beConstructedWith($esClient);
    }

    public function it_is_initializable(): void
    {
        $this->shouldImplement(ProductSubscriptionUpdater::class);
    }

    public function it_updates_a_product_subscribed_to_franklin(Client $esClient): void
    {
        $esClient->updateByQuery([
            'script' => [
                'inline' => 'ctx._source.franklin_subscription = true',
            ],
            'query' => [
                'term' => [
                    'id' => 'product_42',
                ],
            ],
        ])->shouldBeCalled();

        $this->updateSubscribedProduct(new ProductId(42));
    }

    public function it_updates_a_product_unsubscribed_to_franklin(Client $esClient): void
    {
        $esClient->updateByQuery([
            'script' => [
                'inline' => 'ctx._source.franklin_subscription = false',
            ],
            'query' => [
                'term' => [
                    'id' => 'product_42',
                ],
            ],
        ])->shouldBeCalled();

        $this->updateUnsubscribedProduct(new ProductId(42));
    }
}
