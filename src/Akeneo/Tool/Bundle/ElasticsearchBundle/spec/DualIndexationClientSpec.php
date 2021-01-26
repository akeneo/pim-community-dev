<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\DualIndexationClient;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use PhpSpec\ObjectBehavior;

class DualIndexationClientSpec extends ObjectBehavior
{
    function let(
        NativeClient $nativeClient,
        ClientBuilder $clientBuilder,
        Loader $indexConfigurationLoader,
        Client $dualClient
    ) {
        $this->beConstructedWith(
            $clientBuilder,
            $indexConfigurationLoader,
            ['localhost:9200'],
            'an_index_name',
            '',
            $dualClient
        );
        $clientBuilder->setHosts(['localhost:9200'])->willReturn($clientBuilder);
        $clientBuilder->build()->willReturn($nativeClient);
    }

    function it_can_be_instantiated()
    {
        $this->shouldBeAnInstanceOf(DualIndexationClient::class);
        $this->shouldBeAnInstanceOf(Client::class);
    }

    function it_indexes_on_both_clients(NativeClient $nativeClient, Client $dualClient)
    {
        $nativeClient->index(
            [
                'index' => 'an_index_name',
                'id' => 'identifier',
                'body' => ['a key' => 'a value'],
                'refresh' => 'wait_for',
            ]
        )->willReturn(['errors' => false]);
        $dualClient->index('identifier', ['a key' => 'a value'], Refresh::waitFor())->shouldBeCalled();

        $this->index('identifier', ['a key' => 'a value'], Refresh::waitFor())
            ->shouldReturn(['errors' => false]);
    }

    function it_bulk_indexes_on_both_clients(NativeClient $nativeClient, Client $dualClient)
    {
        $expectedResponse = [
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_foo'],
                ['item_bar'],
            ],
        ];

        $nativeClient->bulk([
            'body' => [
                ['index' => [
                    '_index' => 'an_index_name',
                    '_id' => 'foo',
                ]],
                ['identifier' => 'foo', 'name' => 'a name'],
                ['index' => [
                    '_index' => 'an_index_name',
                    '_id' => 'bar',
                ]],
                ['identifier' => 'bar', 'name' => 'a name'],
            ],
            'refresh' => 'wait_for',
        ])->shouldBeCalled()->willReturn($expectedResponse);;

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name'],
        ];

        $dualClient->bulkIndexes($documents, 'identifier', Refresh::waitFor())->shouldBeCalled();

        $this->bulkIndexes($documents, 'identifier', Refresh::waitFor())->shouldReturn($expectedResponse);
    }

    function it_deletes_by_query_on_both_clients(NativeClient $nativeClient, Client $dualClient)
    {
        $query = ['foo' => 'bar'];

        $nativeClient->deleteByQuery([
            'index' => 'an_index_name',
            'body' => $query,
        ])->shouldBeCalled();
        $dualClient->deleteByQuery($query)->shouldBeCalled();

        $this->deleteByQuery($query);
    }

    function it_refreshes_both_indexes(NativeClient $nativeClient, Client $dualClient, IndicesNamespace $indices)
    {
        $nativeClient->indices()->willReturn($indices);
        $indices->refresh(['index' => 'an_index_name'])->willReturn(['errors' => false]);

        $dualClient->refreshIndex()->shouldBeCalled();

        $this->refreshIndex()->shouldReturn(['errors' => false]);
    }
}
