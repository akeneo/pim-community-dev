<?php

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch\CountProductModels;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountProductModelsSpec extends ObjectBehavior
{
    function let(Client $client)
    {
        $this->beConstructedWith($client, 10);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_is_a_count_products_query()
    {
        $this->shouldHaveType(CountProductModels::class);
    }

    function it_gets_the_products_volume(Client $client)
    {
        $client->count(Argument::type('array'))->shouldBeCalled()->willReturn(['count' => 7]);
        $this->fetch()->shouldBeLike(new CountVolume(7, 'count_product_models'));
    }
}
