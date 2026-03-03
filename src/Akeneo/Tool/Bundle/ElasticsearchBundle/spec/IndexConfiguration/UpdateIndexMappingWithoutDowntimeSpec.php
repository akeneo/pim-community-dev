<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client\ClientMigrationInterface;
use Akeneo\Tool\Component\Elasticsearch\ClockInterface;
use PhpSpec\ObjectBehavior;

class UpdateIndexMappingWithoutDowntimeSpec extends ObjectBehavior
{
    private const INDEX_ALIAS_TO_MIGRATE = 'index_alias_to_migrate';
    private const INDEX_NAME_TO_MIGRATE = 'index_name_to_migrate';
    private const MIGRATED_INDEX_NAME = 'migrated_index_name';
    private const TEMPORARY_INDEX_ALIAS = 'temporary_index_alias';

    public function let(
        ClockInterface $clock,
        ClientMigrationInterface $clientMigration,
        IndexConfiguration $indexConfiguration
    ) {
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

        $clientMigration->getIndexSettings(self::INDEX_NAME_TO_MIGRATE)->willReturn([
            'refresh_interval' => 5,
            'number_of_replicas' => 2,
        ]);

        $this->beConstructedWith(
            $clock,
            $clientMigration,
            ['elasticsearch_host']
        );
    }

    public function it_reindex_all_records_on_the_given_index(
        ClockInterface $clock,
        ClientMigrationInterface $clientMigration,
        IndexConfiguration $indexConfiguration
    ) {
        $clientMigration->aliasExist(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);
        $clientMigration->getIndexNameFromAlias(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME],
        );

        $clientMigration->createIndex(self::MIGRATED_INDEX_NAME, [
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1,
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
            'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()]
        ])->shouldBeCalledOnce();

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $clock->now()->willReturn($firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch);

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                ]
            ],
        )->shouldBeCalledOnce()->willReturn(10);

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                ],
            ],
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration
            ->putIndexSetting(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2])
            ->shouldBeCalledTimes(1);

        $clientMigration->refreshIndex(self::MIGRATED_INDEX_NAME)->shouldBeCalledOnce();

        $clientMigration->switchIndexAlias(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        )->shouldBeCalledOnce();

        $clientMigration->reindex(
            self::TEMPORARY_INDEX_ALIAS,
            self::INDEX_ALIAS_TO_MIGRATE,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()],
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration->removeIndex(self::INDEX_NAME_TO_MIGRATE)->shouldBeCalledOnce();

        $this->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ],
            ]
        );
    }

    public function it_handle_index_migration_without_alias(
        ClockInterface $clock,
        ClientMigrationInterface $clientMigration,
        IndexConfiguration $indexConfiguration
    ) {
        $clientMigration->aliasExist(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(false);
        $clientMigration->createAlias('index_alias_to_migrate_migration_alias', self::INDEX_ALIAS_TO_MIGRATE);
        $clientMigration->getIndexNameFromAlias('index_alias_to_migrate_migration_alias')->willReturn(
            [self::INDEX_ALIAS_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME],
        );

        $clientMigration->getIndexSettings(self::INDEX_ALIAS_TO_MIGRATE)->willReturn([
            'refresh_interval' => 5,
            'number_of_replicas' => 2,
        ]);

        $clientMigration->createIndex(self::MIGRATED_INDEX_NAME, [
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1,
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
            'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()]
        ])->shouldBeCalledOnce();

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $clock->now()->willReturn($firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch);

        $clientMigration->reindex(
            'index_alias_to_migrate_migration_alias',
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                ]
            ],
        )->shouldBeCalledOnce()->willReturn(10);

        $clientMigration->reindex(
            'index_alias_to_migrate_migration_alias',
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                ],
            ],
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration
            ->putIndexSetting(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2])
            ->shouldBeCalledTimes(1);

        $clientMigration->refreshIndex(self::MIGRATED_INDEX_NAME)->shouldBeCalledOnce();

        $clientMigration->switchIndexAlias(
            'index_alias_to_migrate_migration_alias',
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        )->shouldBeCalledOnce();

        $clientMigration->reindex(
            self::TEMPORARY_INDEX_ALIAS,
            'index_alias_to_migrate_migration_alias',
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()],
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration->removeIndex(self::INDEX_ALIAS_TO_MIGRATE)->shouldBeCalledOnce();
        $clientMigration->renameAlias('index_alias_to_migrate_migration_alias', self::INDEX_ALIAS_TO_MIGRATE, self::MIGRATED_INDEX_NAME)->shouldBeCalledOnce();

        $this->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ],
            ]
        );
    }

    public function it_reindex_records_updated_during_the_indexation(
        ClockInterface $clock,
        ClientMigrationInterface $clientMigration,
        IndexConfiguration $indexConfiguration
    ) {
        $clientMigration->aliasExist(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);
        $clientMigration->getIndexNameFromAlias(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME]
        );

        $clientMigration->createIndex(self::MIGRATED_INDEX_NAME, [
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1,
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
            'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()]
        ])->shouldBeCalledOnce();

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

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(10);

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                ],
            ],
        )->shouldBeCalledOnce()->willReturn(2);

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterSecondIndexation->modify('- 1second')->getTimestamp()]
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration
            ->putIndexSetting(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2])
            ->shouldBeCalledTimes(1);

        $clientMigration->refreshIndex(self::MIGRATED_INDEX_NAME)->shouldBeCalledOnce();

        $clientMigration->switchIndexAlias(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        )->shouldBeCalledOnce();

        $clientMigration->reindex(
            self::TEMPORARY_INDEX_ALIAS,
            self::INDEX_ALIAS_TO_MIGRATE,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()]
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration->removeIndex(self::INDEX_NAME_TO_MIGRATE)->shouldBeCalledOnce();

        $this->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ],
            ]
        );
    }

    public function it_reindex_records_updated_between_indexation_and_swap(
        ClockInterface $clock,
        ClientMigrationInterface $clientMigration,
        IndexConfiguration $indexConfiguration
    ) {
        $clientMigration->aliasExist(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);
        $clientMigration->getIndexNameFromAlias(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME],
        );

        $clientMigration->createIndex(self::MIGRATED_INDEX_NAME, [
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1,
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
            'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
        ])->shouldBeCalledOnce();

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $firstDatetime->getTimestamp()]
                ],
            ],
        )->shouldBeCalledOnce()->willReturn(10);

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()]
                ],
            ],
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration
            ->putIndexSetting(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2])
            ->shouldBeCalledTimes(1);

        $clientMigration->refreshIndex(self::MIGRATED_INDEX_NAME)->shouldBeCalledOnce();

        $clientMigration->switchIndexAlias(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        )->shouldBeCalledOnce();

        $clientMigration->reindex(
            self::TEMPORARY_INDEX_ALIAS,
            self::INDEX_ALIAS_TO_MIGRATE,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterSwitch->modify('- 1second')->getTimestamp()]
                ],
            ],
        )->shouldBeCalledOnce()->willReturn(1);

        $clientMigration->removeIndex(self::INDEX_NAME_TO_MIGRATE)->shouldBeCalledOnce();

        $this->execute(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME,
            $indexConfiguration,
            fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                ],
            ]
        );
    }

    public function it_does_not_remove_index_while_swap_is_not_done(
        ClockInterface $clock,
        ClientMigrationInterface $clientMigration,
        IndexConfiguration $indexConfiguration
    ) {
        $clientMigration->aliasExist(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(true);
        $clientMigration->getIndexNameFromAlias(self::INDEX_ALIAS_TO_MIGRATE)->willReturn(
            [self::INDEX_NAME_TO_MIGRATE],
            [self::MIGRATED_INDEX_NAME],
        );

        $clientMigration->createIndex(self::MIGRATED_INDEX_NAME, [
            'settings' => [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1,
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
            'aliases' => [self::TEMPORARY_INDEX_ALIAS => new \stdClass()],
        ])->shouldBeCalledOnce();

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $firstDatetime->getTimestamp()],
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(10);

        $clientMigration->reindex(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            [
                'range' => [
                    'updated_at' => ['gt' => $datetimeAfterFirstIndexation->modify('- 1second')->getTimestamp()],
                ],
            ]
        )->shouldBeCalledOnce()->willReturn(0);

        $clientMigration
            ->putIndexSetting(self::MIGRATED_INDEX_NAME, ['refresh_interval' => 5, 'number_of_replicas' => 2])
            ->shouldBeCalledTimes(1);

        $clientMigration->refreshIndex(self::MIGRATED_INDEX_NAME)->shouldBeCalledOnce();

        $clientMigration->switchIndexAlias(
            self::INDEX_ALIAS_TO_MIGRATE,
            self::INDEX_NAME_TO_MIGRATE,
            self::TEMPORARY_INDEX_ALIAS,
            self::MIGRATED_INDEX_NAME
        )->shouldBeCalledOnce()->willThrow(\InvalidArgumentException::class);

        $clientMigration->removeIndex(self::INDEX_NAME_TO_MIGRATE)->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('execute', [
                self::INDEX_ALIAS_TO_MIGRATE,
                self::TEMPORARY_INDEX_ALIAS,
                self::MIGRATED_INDEX_NAME,
                $indexConfiguration,
                fn (\DateTimeImmutable $referenceDatetime) => [
                    'range' => [
                        'updated_at' => ['gt' => $referenceDatetime->getTimestamp()]
                    ]
                ]
            ]);
    }
}
