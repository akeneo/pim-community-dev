<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
    }

    function let(NativeClient $client, ClientBuilder $clientBuilder, Loader $indexConfigurationLoader)
    {
        $this->beConstructedWith($clientBuilder, $indexConfigurationLoader, ['localhost:9200'], 'an_index_name');
        $clientBuilder->setHosts(Argument::any())->willReturn($clientBuilder);
        $clientBuilder->build()->willReturn($client);
    }

    public function it_indexes_a_document($client)
    {
        $client->index(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'id'    => 'identifier',
                'body'  => ['a key' => 'a value'],
                'refresh' => 'wait_for',
            ]
        )->willReturn(['errors' => false]);

        $this->index('an_index_type', 'identifier', ['a key' => 'a value'], Refresh::waitFor());
    }

    public function it_triggers_an_exception_during_the_indexation_of_a_document($client)
    {
        $client->index(Argument::any())->willThrow(\Exception::class);

        $this->shouldThrow(IndexationException::class)->during(
            'index',
            ['an_index_type', 'identifier', ['a key' => 'a value'], Refresh::waitFor()]
        );
    }

    public function it_triggers_an_exception_if_the_indexation_of_a_document_has_failed($client)
    {
        $client->index(['a document'])->willReturn(
            [
                'errors' => true,
                'items' => [
                    ['index' => ['error' => 'foo']]
                ]
            ]
        );

        $this->shouldThrow(IndexationException::class)->during(
            'index',
            ['an_index_type', 'identifier', ['a document'], Refresh::waitFor()]
        );
    }

    public function it_gets_a_document($client)
    {
        $client->get(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'id'    => 'identifier',
            ]
        )->shouldBeCalled();

        $this->get('an_index_type', 'identifier');
    }

    public function it_searches_documents($client)
    {
        $client->search(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'body'  => ['a key' => 'a value']
            ]
        )->shouldBeCalled();

        $this->search('an_index_type', ['a key' => 'a value']);
    }

    public function it_deletes_a_document($client)
    {
        $client->delete(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'id'    => 'identifier',
            ]
        )->shouldBeCalled();

        $this->delete('an_index_type', 'identifier');
    }

    public function it_bulk_deletes_documents($client)
    {
        $client->bulk(
            [
                'body' => [
                    [
                        'delete' => [
                            '_index' => 'an_index_name',
                            '_type' => 'an_index_type',
                            '_id' => 40
                        ],
                    ],
                    [
                        'delete' => [
                            '_index' => 'an_index_name',
                            '_type' => 'an_index_type',
                            '_id' => 33
                        ],
                    ],
                ]
            ]
        )->shouldBeCalled();

        $this->bulkDelete('an_index_type', [40, 33]);
    }

    public function it_deletes_an_index($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->delete(['index' => 'an_index_name'])->shouldBeCalled();

        $this->deleteIndex();
    }

    public function it_creates_an_index(
        $client,
        $indexConfigurationLoader,
        IndexConfiguration $indexConfiguration,
        IndicesNamespace $indices
    ) {
        $indexConfigurationLoader->load()->willReturn($indexConfiguration);
        $indexConfiguration->buildAggregated()->willReturn(['index configuration']);

        $client->indices()->willReturn($indices);
        $indices->create(
            [
                'index' => 'an_index_name',
                'body'  => ['index configuration'],
            ]
        )->shouldBeCalled();

        $this->createIndex();
    }

    function it_checks_if_an_index_exists($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->exists(['index' => 'an_index_name'])->shouldBeCalled();

        $this->hasIndex();
    }

    function it_refreshes_an_index($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->refresh(['index' => 'an_index_name'])->shouldBeCalled();

        $this->refreshIndex();
    }

    function it_indexes_with_bulk_several_documents($client)
    {
        $client->bulk(
            [
                'body' => [
                    ['index' => [
                        '_index' => 'an_index_name',
                        '_type' => 'an_index_type',
                        '_id' => 'foo'
                    ]],
                    ['identifier' => 'foo', 'name' => 'a name'],
                    ['index' => [
                        '_index' => 'an_index_name',
                        '_type' => 'an_index_type',
                        '_id' => 'bar'
                    ]],
                    ['identifier' => 'bar', 'name' => 'a name']
                ],
                'refresh' => 'wait_for'
            ]
        )->willReturn(['errors' => false]);;

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name']
        ];

        $this->bulkIndexes('an_index_type', $documents, 'identifier', Refresh::waitFor());
    }

    public function it_throws_an_exception_during_the_indexation_of_several_documents($client)
    {
        $client->bulk(Argument::any())->willThrow(\Exception::class);

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name']
        ];

        $this->shouldThrow(IndexationException::class)->during(
            'bulkIndexes',
            ['an_index_type', $documents, 'identifier', Refresh::waitFor()]
        );
    }

    public function it_triggers_an_exception_if_the_indexation_of_one_document_among_several_has_failed($client)
    {
        $client->bulk(Argument::any())->willReturn(
            [
                'errors' => true,
                'items' => [
                    ['index' => []],
                    ['index' => ['error' => 'foo']]                ]
            ]
        );

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name']
        ];

        $this->shouldThrow(IndexationException::class)->during(
            'bulkIndexes',
            ['an_index_type', $documents, 'identifier', Refresh::waitFor()]
        );
    }

    function it_throws_an_exception_if_identifier_key_is_missing($client)
    {
        $client->bulk(Argument::any())->shouldNotBeCalled();

        $documents = [
            ['name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name']
        ];

        $this->shouldThrow(new MissingIdentifierException('Missing "identifier" key in document'))
            ->during('bulkIndexes', ['an_index_type', $documents, 'identifier', Refresh::waitFor()]);
    }
}
