<?php

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch\CountProducts;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountProductsSpec extends ObjectBehavior
{
    function let(Client $client)
    {
        $this->beConstructedWith($client, 100);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_is_a_count_variant_products_query()
    {
        $this->shouldHaveType(CountProducts::class);
    }

    function it_gets_the_products_volume(Client $client)
    {
        $client->count(Argument::type('array'))->shouldBeCalled()->willReturn(['count' => 75]);
        $this->fetch()->shouldBeLike(new CountVolume(75, 'count_products'));
    }
}
