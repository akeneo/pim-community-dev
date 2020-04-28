<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
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
                'id' => 'identifier',
                'body' => ['a key' => 'a value'],
                'refresh' => 'wait_for',
            ]
        )->willReturn(['errors' => false]);

        $this->index('identifier', ['a key' => 'a value'], Refresh::waitFor());
    }

    public function it_triggers_an_exception_during_the_indexation_of_a_document($client)
    {
        $client->index(Argument::any())->willThrow(\Exception::class);

        $this->shouldThrow(IndexationException::class)->during(
            'index',
            ['identifier', ['a key' => 'a value'], Refresh::waitFor()]
        );
    }

    public function it_triggers_an_exception_if_the_indexation_of_a_document_has_failed($client)
    {
        $client->index(Argument::type('array'))->willReturn(
            [
                'errors' => true,
                'items' => [
                    ['index' => ['error' => 'foo']],
                ],
            ]
        );

        $this->shouldThrow(IndexationException::class)->during(
            'index',
            ['identifier', ['a document'], Refresh::waitFor()]
        );
    }

    public function it_gets_a_document($client)
    {
        $client->get(
            [
                'index' => 'an_index_name',
                'id' => 'identifier',
            ]
        )->shouldBeCalled();

        $this->get('identifier');
    }

    public function it_searches_documents($client)
    {
        $client->search(
            [
                'index' => 'an_index_name',
                'body' => ['a key' => 'a value'],
            ]
        )->shouldBeCalled();

        $this->search(['a key' => 'a value']);
    }

    public function it_multi_searches_documents($client)
    {
        $client->msearch(
            [
                'index' => 'an_index_name',
                'body' => [
                    ['index' => 'another_index_name'],
                    ['size' => 0, 'query' => ['match_all' => (object) []]],
                    [],
                    ['size' => 0, 'query' => ['match_all' => (object) []]],
                ],
            ]
        )->willReturn([
            [
                'took' => 51,
                'timed_out' => false,
                '_shards' => [
                    'total' => 5,
                    'successful' => 5,
                    'failed' => 0,
                ],
                [
                    'took' => 53,
                    'timed_out' => false,
                    '_shards' => [
                        'total' => 7,
                        'successful' => 5,
                        'failed' => 0,
                    ],
                ],
            ],
        ]);

        $this->msearch([
            ['index' => 'another_index_name'],
            ['size' => 0, 'query' => ['match_all' => (object) []]],
            [],
            ['size' => 0, 'query' => ['match_all' => (object) []]],
        ])->shouldReturn([
            [
                'took' => 51,
                'timed_out' => false,
                '_shards' => [
                    'total' => 5,
                    'successful' => 5,
                    'failed' => 0,
                ],
                [
                    'took' => 53,
                    'timed_out' => false,
                    '_shards' => [
                        'total' => 7,
                        'successful' => 5,
                        'failed' => 0,
                    ],
                ],
            ],
        ]);
    }

    public function it_deletes_a_document($client)
    {
        $client->delete(
            [
                'index' => 'an_index_name',
                'id' => 'identifier',
            ]
        )->shouldBeCalled();

        $this->delete('identifier');
    }

    public function it_bulk_deletes_documents($client)
    {
        $client->bulk(
            [
                'body' => [
                    [
                        'delete' => [
                            '_index' => 'an_index_name',
                            '_id' => 40,
                        ],
                    ],
                    [
                        'delete' => [
                            '_index' => 'an_index_name',
                            '_id' => 33,
                        ],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->bulkDelete([40, 33]);
    }

    public function it_deletes_an_index_without_alias($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->existsAlias(['name' => 'an_index_name'])->willReturn(false);
        $indices->delete(['index' => 'an_index_name'])->shouldBeCalled();

        $this->deleteIndex();
    }

    public function it_deletes_an_index_with_alias($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->existsAlias(['name' => 'an_index_name'])->willReturn(true);
        $expectedAlias = [
            'an_index_name_foo_20190514' => [
                'an_index_name' => ['index_data']
            ]
        ];
        $indices->getAlias(['name' => 'an_index_name'])->willReturn($expectedAlias);
        $indices->delete(['index' => 'an_index_name_foo_20190514'])->shouldBeCalled();

        $this->deleteIndex();
    }

    function it_checks_if_an_index_exists($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->exists(['index' => 'an_index_name'])->willReturn(true);

        $this->hasIndex()->shouldReturn(true);
    }

    function it_checks_if_an_alias_exists($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->existsAlias(['name' => 'an_index_name'])->willReturn(true);

        $this->hasIndexForAlias()->shouldReturn(true);
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
            ]
        )->willReturn(['errors' => false]);;

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name'],
        ];

        $this->bulkIndexes($documents, 'identifier', Refresh::waitFor());
    }

    public function it_throws_an_exception_during_the_indexation_of_several_documents($client)
    {
        $client->bulk(Argument::any())->willThrow(\Exception::class);

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name'],
        ];

        $this->shouldThrow(IndexationException::class)->during(
            'bulkIndexes',
            [$documents, 'identifier', Refresh::waitFor()]
        );
    }

    public function it_triggers_an_exception_if_the_indexation_of_one_document_among_several_has_failed($client)
    {
        $client->bulk(Argument::any())->willReturn(
            [
                'errors' => true,
                'items' => [
                    ['index' => []],
                    ['index' => ['error' => 'foo']]],
            ]
        );

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name'],
        ];

        $this->shouldThrow(IndexationException::class)->during(
            'bulkIndexes',
            [$documents, 'identifier', Refresh::waitFor()]
        );
    }

    function it_throws_an_exception_if_identifier_key_is_missing($client)
    {
        $client->bulk(Argument::any())->shouldNotBeCalled();

        $documents = [
            ['name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name'],
        ];

        $this->shouldThrow(new MissingIdentifierException('Missing "identifier" key in document'))
            ->during('bulkIndexes', [$documents, 'identifier', Refresh::waitFor()]);
    }
}
