<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Component\Elasticsearch\ClockInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use PhpSpec\ObjectBehavior;

class UpdateIndexMappingWithoutDowntimeSpec extends ObjectBehavior
{
    public function let(
        ClockInterface $clock,
        ClientBuilder $clientBuilder,
        Client $client,
        IndicesNamespace $indicesNamespace,
        IndexConfiguration $indexConfiguration
    ) {
        $clientBuilder->setHosts(['elasticsearch_host'])->shouldBeCalled()->willReturn($clientBuilder);
        $clientBuilder->build()->willReturn($client);
        $client->indices()->willReturn($indicesNamespace);

        $indexConfiguration->buildAggregated()->willReturn([
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 2,
                ],
            ],
            'mappings' => [
                'properties' => [
                    'name' => [
                        'properties' => [
                            'last' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $indicesNamespace->getSettings(['index' => 'index_name_to_migrate'])->willReturn([
            'index_name_to_migrate' => [
                'settings' => [
                    'index' => [
                        'refresh_interval' => 5,
                        'number_of_replicas' => 2,
                    ],
                ],
            ],
        ]);

        $this->beConstructedWith(
            $clock,
            $clientBuilder,
            ['elasticsearch_host']
        );
    }

    public function it_reindex_all_records_on_the_given_index(
        ClockInterface $clock,
        CLient $client,
        IndicesNamespace $indicesNamespace,
        IndexConfiguration $indexConfiguration
    ) {
        $indicesNamespace->getAlias(['name' => 'index_alias_to_migrate'])->willReturn(
            ['index_name_to_migrate' => 'index_alias_to_migrate'],
            ['migrated_index_name' => 'index_alias_to_migrate']
        );

        $indicesNamespace->create([
            'index' => 'migrated_index_name',
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 0,
                        'refresh_interval' => -1
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'properties' => [
                                'last' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                'aliases' => ['temporary_index_alias' => new \stdClass()]
            ]
        ])->willReturn(['acknowledged' => true]);

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $clock->now()->willReturn($firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 10]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 0]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => ['index' => ['refresh_interval' => 5, 'number_of_replicas' => 2]]])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'index_alias_to_migrate', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'index_alias_to_migrate', 'index' => 'index_name_to_migrate'],
                    ],
                    [
                        'add' => ['alias' => 'temporary_index_alias', 'index' => 'index_name_to_migrate']
                    ],
                    [
                        'remove' => ['alias' => 'temporary_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => true]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'temporary_index_alias',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'index_alias_to_migrate',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 0]);

        $indicesNamespace->delete(['index' => 'index_name_to_migrate'])->willReturn(['acknowledged' => true]);

        $this->execute(
            'index_alias_to_migrate',
            'temporary_index_alias',
            'migrated_index_name',
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ]
            ]
        );
    }

    public function it_reindex_records_updated_during_the_indexation(
        ClockInterface $clock,
        Client $client,
        IndicesNamespace $indicesNamespace,
        IndexConfiguration $indexConfiguration
    ) {
        $indicesNamespace->getAlias(['name' => 'index_alias_to_migrate'])->willReturn(
            ['index_name_to_migrate' => 'index_alias_to_migrate'],
            ['migrated_index_name' => 'index_alias_to_migrate']
        );

        $indicesNamespace->create([
            'index' => 'migrated_index_name',
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 0,
                        'refresh_interval' => -1
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'properties' => [
                                'last' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                'aliases' => ['temporary_index_alias' => new \stdClass()]
            ]
        ])->shouldBeCalled()->willReturn(['acknowledged' => true]);

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSecondIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSecondIndexation,
            $datetimeAfterSwitch
        );

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 10]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 2]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSecondIndexation->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 0]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => ['index' => ['refresh_interval' => 5, 'number_of_replicas' => 2]]])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'index_alias_to_migrate', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'index_alias_to_migrate', 'index' => 'index_name_to_migrate'],
                    ],
                    [
                        'add' => ['alias' => 'temporary_index_alias', 'index' => 'index_name_to_migrate']
                    ],
                    [
                        'remove' => ['alias' => 'temporary_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => true]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'temporary_index_alias',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'index_alias_to_migrate',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 0]);

        $indicesNamespace->delete(['index' => 'index_name_to_migrate'])->willReturn(['acknowledged' => true]);

        $this->execute(
            'index_alias_to_migrate',
            'temporary_index_alias',
            'migrated_index_name',
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ]
            ]
        );
    }

    public function it_reindex_records_updated_between_indexation_and_swap(
        ClockInterface $clock,
        Client $client,
        IndicesNamespace $indicesNamespace,
        IndexConfiguration $indexConfiguration
    ) {
        $indicesNamespace->getAlias(['name' => 'index_alias_to_migrate'])->willReturn(
            ['index_name_to_migrate' => 'index_alias_to_migrate'],
            ['migrated_index_name' => 'index_alias_to_migrate']
        );

        $indicesNamespace->create([
            'index' => 'migrated_index_name',
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 0,
                        'refresh_interval' => -1
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'properties' => [
                                'last' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                'aliases' => ['temporary_index_alias' => new \stdClass()]
            ]
        ])->shouldBeCalled()->willReturn(['acknowledged' => true]);

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 10]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 0]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => ['index' => ['refresh_interval' => 5, 'number_of_replicas' => 2]]])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'index_alias_to_migrate', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'index_alias_to_migrate', 'index' => 'index_name_to_migrate'],
                    ],
                    [
                        'add' => ['alias' => 'temporary_index_alias', 'index' => 'index_name_to_migrate']
                    ],
                    [
                        'remove' => ['alias' => 'temporary_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => true]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'temporary_index_alias',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'index_alias_to_migrate',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 1]);

        $indicesNamespace->delete(['index' => 'index_name_to_migrate'])->willReturn(['acknowledged' => true]);

        $this->execute(
            'index_alias_to_migrate',
            'temporary_index_alias',
            'migrated_index_name',
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ]
            ]
        );
    }

    public function it_does_not_remove_index_while_swap_is_not_done(
        ClockInterface $clock,
        Client $client,
        IndicesNamespace $indicesNamespace,
        IndexConfiguration $indexConfiguration
    ) {
        $indicesNamespace->getAlias(['name' => 'index_alias_to_migrate'])->willReturn(
            ['index_name_to_migrate' => 'index_alias_to_migrate'],
            ['migrated_index_name' => 'index_alias_to_migrate']
        );

        $indicesNamespace->create([
            'index' => 'migrated_index_name',
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 0,
                        'refresh_interval' => -1
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'properties' => [
                                'last' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                'aliases' => ['temporary_index_alias' => new \stdClass()]
            ]
        ])->shouldBeCalled()->willReturn(['acknowledged' => true]);

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $firstDatetime->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 2]);

        $client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => 'index_alias_to_migrate',
                    "query" => [
                        'range' => [
                            'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()]
                        ]
                    ],
                ],
                "dest" => [
                    "index" => 'temporary_index_alias',
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn(['total' => 0]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => ['index' => ['refresh_interval' => 5, 'number_of_replicas' => 2]]])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'index_alias_to_migrate', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'index_alias_to_migrate', 'index' => 'index_name_to_migrate'],
                    ],
                    [
                        'add' => ['alias' => 'temporary_index_alias', 'index' => 'index_name_to_migrate']
                    ],
                    [
                        'remove' => ['alias' => 'temporary_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => false]);

        $indicesNamespace->delete(['index' => 'index_name_to_migrate'])->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Index switch is not acknowledged'))
            ->during('execute', [
                'index_alias_to_migrate',
                'temporary_index_alias',
                'migrated_index_name',
                $indexConfiguration,
                fn (\DateTimeImmutable $referenceDatetime) => [
                    'range' => [
                        'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                    ]
                ]
            ]);
    }
}
