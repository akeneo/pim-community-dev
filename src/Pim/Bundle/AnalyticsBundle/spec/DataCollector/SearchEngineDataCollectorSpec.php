<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\AnalyticsBundle\DataCollector\SearchEngineDataCollector;
use Prophecy\Argument;

class SearchEngineDataCollectorSpec extends ObjectBehavior
{
    function let(ClientBuilder $clientBuilder)
    {
        $this->beConstructedWith($clientBuilder, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchEngineDataCollector::class);
        $this->shouldHaveType(DataCollectorInterface::class);
    }

    function it_collects_search_engine_version($clientBuilder, Client $client)
    {
        $clientBuilder->setHosts(Argument::type('array'))->willReturn($clientBuilder);
        $clientBuilder->build()->willReturn($client);

        $client->info()->willReturn(
            [
                'version' => [
                    'number' => '1.2.3',
                ],
            ]
        );

        $this->collect()->shouldReturn(
            [
                'elasticsearch_version' => '1.2.3',
            ]
        );
    }
}
