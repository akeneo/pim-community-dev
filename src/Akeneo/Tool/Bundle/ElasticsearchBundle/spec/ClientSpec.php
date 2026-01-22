<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Elastic\Elasticsearch\Client as NativeClient;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Endpoints\Indices;
use Elastic\Elasticsearch\Response\Elasticsearch as ElasticsearchResponse;
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

    public function it_indexes_a_document($client, ElasticsearchResponse $response)
    {
        $client->index(
            [
                'index' => 'an_index_name',
                'id' => 'identifier',
                'body' => ['a key' => 'a value'],
                'refresh' => 'wait_for',
            ]
        )->willReturn($response);
        $response->asArray()->willReturn(['errors' => false]);

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

    public function it_triggers_an_exception_if_the_indexation_of_a_document_has_failed($client, ElasticsearchResponse $response)
    {
        $client->index(Argument::type('array'))->willReturn($response);
        $response->asArray()->willReturn(
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

    public function it_gets_a_document($client, ElasticsearchResponse $response)
    {
        $client->get(
            [
                'index' => 'an_index_name',
                'id' => 'identifier',
            ]
        )->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['_source' => []]);

        $this->get('identifier');
    }

    public function it_searches_documents($client, ElasticsearchResponse $response)
    {
        $client->search(
            [
                'index' => 'an_index_name',
                'body' => ['a key' => 'a value'],
            ]
        )->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['hits' => []]);

        $this->search(['a key' => 'a value']);
    }

    function it_counts_documents($client, ElasticsearchResponse $response)
    {
        $client->count(
            [
                'index' => 'an_index_name',
                'body' => ['query' => 'some_query']
            ]
        )->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['count' => 42]);

        $this->count(['query' => 'some_query'])->shouldReturn(['count' => 42]);
    }

    public function it_multi_searches_documents($client, ElasticsearchResponse $response)
    {
        $expectedResult = [
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
        ];

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
        )->willReturn($response);
        $response->asArray()->willReturn($expectedResult);

        $this->msearch([
            ['index' => 'another_index_name'],
            ['size' => 0, 'query' => ['match_all' => (object) []]],
            [],
            ['size' => 0, 'query' => ['match_all' => (object) []]],
        ])->shouldReturn($expectedResult);
    }

    public function it_deletes_a_document($client, ElasticsearchResponse $response)
    {
        $client->delete(
            [
                'index' => 'an_index_name',
                'id' => 'identifier',
            ]
        )->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['result' => 'deleted']);

        $this->delete('identifier');
    }

    public function it_bulk_deletes_documents($client, ElasticsearchResponse $response)
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
        )->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['errors' => false]);

        $this->bulkDelete([40, 33]);
    }

    public function it_bulk_updates_documents($client, ElasticsearchResponse $response)
    {
        $client->bulk(
            [
                'body' => [
                    [
                        'update' => [
                            '_index' => 'an_index_name',
                            '_id' => '40',
                        ],
                    ],
                    'params_of_id_40',
                    [
                        'update' => [
                            '_index' => 'an_index_name',
                            '_id' => '33',
                        ],
                    ],
                    'params_of_id_33',
                ],
            ]
        )->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['errors' => false]);

        $this->bulkUpdate(['40', '33'], ['40' => 'params_of_id_40', '33' => 'params_of_id_33']);
    }

    public function it_deletes_an_index_without_alias($client, Indices $indices, ElasticsearchResponse $aliasResponse, ElasticsearchResponse $deleteResponse)
    {
        $client->indices()->willReturn($indices);
        $indices->existsAlias(['name' => 'an_index_name'])->willReturn($aliasResponse);
        $aliasResponse->asBool()->willReturn(false);
        $indices->delete(['index' => 'an_index_name'])->shouldBeCalled()->willReturn($deleteResponse);
        $deleteResponse->asArray()->willReturn(['acknowledged' => true]);

        $this->deleteIndex();
    }

    public function it_deletes_an_index_with_alias($client, Indices $indices, ElasticsearchResponse $aliasResponse, ElasticsearchResponse $getAliasResponse, ElasticsearchResponse $deleteResponse)
    {
        $client->indices()->willReturn($indices);
        $indices->existsAlias(['name' => 'an_index_name'])->willReturn($aliasResponse);
        $aliasResponse->asBool()->willReturn(true);
        $expectedAlias = [
            'an_index_name_foo_20190514' => [
                'an_index_name' => ['index_data']
            ]
        ];
        $indices->getAlias(['name' => 'an_index_name'])->willReturn($getAliasResponse);
        $getAliasResponse->asArray()->willReturn($expectedAlias);
        $indices->delete(['index' => 'an_index_name_foo_20190514'])->shouldBeCalled()->willReturn($deleteResponse);
        $deleteResponse->asArray()->willReturn(['acknowledged' => true]);

        $this->deleteIndex();
    }

    function it_checks_if_an_index_exists($client, Indices $indices, ElasticsearchResponse $response)
    {
        $client->indices()->willReturn($indices);
        $indices->exists(['index' => 'an_index_name'])->willReturn($response);
        $response->asBool()->willReturn(true);

        $this->hasIndex()->shouldReturn(true);
    }

    function it_checks_if_an_alias_exists($client, Indices $indices, ElasticsearchResponse $response)
    {
        $client->indices()->willReturn($indices);
        $indices->existsAlias(['name' => 'an_index_name'])->willReturn($response);
        $response->asBool()->willReturn(true);

        $this->hasIndexForAlias()->shouldReturn(true);
    }

    function it_refreshes_an_index($client, Indices $indices, ElasticsearchResponse $response)
    {
        $client->indices()->willReturn($indices);
        $indices->refresh(['index' => 'an_index_name'])->shouldBeCalled()->willReturn($response);
        $response->asArray()->willReturn(['_shards' => ['total' => 1, 'successful' => 1, 'failed' => 0]]);

        $this->refreshIndex();
    }

    function it_indexes_with_bulk_several_documents($client, ElasticsearchResponse $response)
    {
        $expectedResponse = [
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_foo'],
                ['item_bar'],
            ],
        ];

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
        )->shouldBeCalledOnce()->willReturn($response);
        $response->asArray()->willReturn($expectedResponse);

        $documents = [
            ['identifier' => 'foo', 'name' => 'a name'],
            ['identifier' => 'bar', 'name' => 'a name'],
        ];

        $this->bulkIndexes($documents, 'identifier', Refresh::waitFor())->shouldReturn($expectedResponse);
    }

    function it_split_bulk_index_when_size_is_more_than_max_batch_size(
        NativeClient $client,
        ClientBuilder $clientBuilder,
        Loader $indexConfigurationLoader,
        ElasticsearchResponse $response1,
        ElasticsearchResponse $response2,
        ElasticsearchResponse $response3
    ) {
        $this->beConstructedWith($clientBuilder, $indexConfigurationLoader, ['localhost:9200'], 'an_index_name', '', 200);

        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                ['identifier' => 'value1', 'name' => 'name1'],
                ['index' => ['_index' => 'an_index_name', '_id' => 'value2']],
                ['identifier' => 'value2', 'name' => 'name2'],
            ],
            'refresh' => 'wait_for',
        ])->shouldBeCalledTimes(1)->willReturn($response1);
        $response1->asArray()->willReturn([
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_value1'],
                ['item_value2'],
            ],
        ]);

        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value3']],
                ['identifier' => 'value3', 'name' => 'name3'],
                ['index' => ['_index' => 'an_index_name', '_id' => 'value4']],
                ['identifier' => 'value4', 'name' => 'name4'],
            ],
            'refresh' => 'wait_for',
        ])->shouldBeCalledTimes(1)->willReturn($response2);
        $response2->asArray()->willReturn([
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_value3'],
                ['item_value4'],
            ],
        ]);

        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value5']],
                ['identifier' => 'value5', 'name' => 'name5'],
            ],
            'refresh' => 'wait_for',
        ])->shouldBeCalledTimes(1)->willReturn($response3);
        $response3->asArray()->willReturn([
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_value5'],
            ],
        ]);

        $documents = [
            ['identifier' => 'value1', 'name' => 'name1'],
            ['identifier' => 'value2', 'name' => 'name2'],
            ['identifier' => 'value3', 'name' => 'name3'],
            ['identifier' => 'value4', 'name' => 'name4'],
            ['identifier' => 'value5', 'name' => 'name5'],
        ];

        $this->bulkIndexes($documents, 'identifier', Refresh::waitFor())->shouldReturn([
            'took' => 3,
            'errors' => false,
            'items' => [
                ['item_value1'],
                ['item_value2'],
                ['item_value3'],
                ['item_value4'],
                ['item_value5'],
            ],
        ]);
    }

    function it_retries_bulk_index_request_when_an_error_occurred(NativeClient $client)
    {
        $expectedResponse = ['took' => 1, 'errors' => false, 'items' => [['item_value1']]];
        $mockResponse = new class($expectedResponse) {
            private array $data;
            public function __construct(array $data) { $this->data = $data; }
            public function asArray(): array { return $this->data; }
        };

        $isFirstCall = true;
        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                ['identifier' => 'value1', 'name' => 'name1'],
            ],
            'refresh' => 'wait_for',
        ])
        ->shouldBeCalledTimes(2)
        ->will(function () use (&$isFirstCall, $mockResponse) {
            if ($isFirstCall) {
                $isFirstCall = false;
                throw new ClientResponseException('Bad Request', 400);
            }

            return $mockResponse;
        });

        $documents = [['identifier' => 'value1', 'name' => 'name1']];

        $this->bulkIndexes($documents, 'identifier', Refresh::waitFor())->shouldReturn([
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_value1'],
            ],
        ]);
    }

    function it_retries_bulk_index_request_by_splitting_body_when_an_error_occurred(NativeClient $client, ElasticsearchResponse $response1, ElasticsearchResponse $response2)
    {
        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                ['identifier' => 'value1', 'name' => 'name1'],
                ['index' => ['_index' => 'an_index_name', '_id' => 'value2']],
                ['identifier' => 'value2', 'name' => 'name2'],
                ['index' => ['_index' => 'an_index_name', '_id' => 'value3']],
                ['identifier' => 'value3', 'name' => 'name3'],
            ],
            'refresh' => 'wait_for',
        ])->willThrow(new ClientResponseException('Bad Request', 400));

        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                ['identifier' => 'value1', 'name' => 'name1'],
                ['index' => ['_index' => 'an_index_name', '_id' => 'value2']],
                ['identifier' => 'value2', 'name' => 'name2'],
            ],
            'refresh' => 'wait_for',
        ])->shouldBeCalledTimes(1)->willReturn($response1);
        $response1->asArray()->willReturn([
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_value1'],
                ['item_value2'],
            ],
        ]);

        $client->bulk([
            'body' => [
                ['index' => ['_index' => 'an_index_name', '_id' => 'value3']],
                ['identifier' => 'value3', 'name' => 'name3'],
            ],
            'refresh' => 'wait_for',
        ])->shouldBeCalledTimes(1)->willReturn($response2);
        $response2->asArray()->willReturn([
            'took' => 1,
            'errors' => false,
            'items' => [
                ['item_value3'],
            ],
        ]);

        $documents = [
            ['identifier' => 'value1', 'name' => 'name1'],
            ['identifier' => 'value2', 'name' => 'name2'],
            ['identifier' => 'value3', 'name' => 'name3'],
        ];

        $this->bulkIndexes($documents, 'identifier', Refresh::waitFor())->shouldReturn([
            'took' => 2,
            'errors' => false,
            'items' => [
                ['item_value1'],
                ['item_value2'],
                ['item_value3'],
            ],
        ]);
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

    public function it_triggers_an_exception_if_the_indexation_of_one_document_among_several_has_failed($client, ElasticsearchResponse $response)
    {
        $client->bulk(Argument::any())->willReturn($response);
        $response->asArray()->willReturn(
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
