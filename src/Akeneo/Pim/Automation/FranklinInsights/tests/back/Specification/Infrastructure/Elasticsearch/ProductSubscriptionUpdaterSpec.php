<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\ProductSubscriptionUpdater;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use PhpSpec\ObjectBehavior;

class ProductSubscriptionUpdaterSpec extends ObjectBehavior
{
    public function let(ClientBuilder $clientBuilder, Client $client): void
    {
        $hosts = ['localhost: 9200'];
        $clientBuilder->setHosts($hosts)->shouldBeCalled();
        $clientBuilder->build()->willReturn($client);

        $this->beConstructedWith($clientBuilder, $hosts, 'akeneo_pim_product_and_product_model');
    }

    public function it_is_initializable(): void
    {
        $this->shouldImplement(ProductSubscriptionUpdater::class);
    }

    public function it_updates_a_product_subscribed_to_franklin(Client $client): void
    {
        $client->updateByQuery([
            'index' => 'akeneo_pim_product_and_product_model',
            'type' => 'pim_catalog_product',
            'body' => [
                'script' => [
                    'inline' => 'ctx._source.franklin_subscription = true',
                ],
                'query' => [
                    'term' => [
                        'id' => 'product_42',
                    ],
                ],
            ],
        ])->shouldBeCalled();

        $this->updateSubscribedProduct(new ProductId(42));
    }

    public function it_updates_a_product_unsubscribed_to_franklin(Client $client): void
    {
        $client->updateByQuery([
            'index' => 'akeneo_pim_product_and_product_model',
            'type' => 'pim_catalog_product',
            'body' => [
                'script' => [
                    'inline' => 'ctx._source.franklin_subscription = false',
                ],
                'query' => [
                    'term' => [
                        'id' => 'product_42',
                    ],
                ],
            ],
        ])->shouldBeCalled();

        $this->updateUnsubscribedProduct(new ProductId(42));
    }
}
