<?php

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch\CountVariantProducts;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountVariantProductsSpec extends ObjectBehavior
{
    function let(Client $client)
    {
        $this->beConstructedWith($client, 50);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_is_a_count_variant_products_query()
    {
        $this->shouldHaveType(CountVariantProducts::class);
    }

    function it_gets_the_products_volume(Client $client)
    {
        $client->count(Argument::type('array'))->shouldBeCalled()->willReturn(['count' => 33]);
        $this->fetch()->shouldBeLike(new CountVolume(33, 'count_variant_products'));
    }
}
