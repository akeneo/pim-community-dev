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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\IndexMigration;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Clock\ClockInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\GetRecordIdentifiersUpdatedAfterDatetime;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class ReindexRecordsWithoutDowntimeSpec extends ObjectBehavior
{
    public function let(
        ClientBuilder $clientBuilder,
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        NativeClient $nativeClient,
        IndicesNamespace $indicesNamespace,
        Client $currentIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime,
        LoggerInterface $logger
    ) {
        $clientBuilder->setHosts(['elasticsearch_host'])->shouldBeCalled()->willReturn($clientBuilder);
        $clientBuilder->build()->willReturn($nativeClient);
        $nativeClient->indices()->willReturn($indicesNamespace);

        $this->beConstructedWith(
            $clientBuilder,
            $clock,
            $recordIndexationNormalizer,
            $currentIndexRecordClient,
            $getRecordIdentifiersUpdatedAfterDatetime,
            $logger,
            'record_index_alias',
            ['elasticsearch_host'],
            10
        );
    }

    public function it_reindex_all_records_on_the_given_index(
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        IndicesNamespace $indicesNamespace,
        Client $currentIndexRecordClient,
        Client $migratedIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime
    ) {
        $indicesNamespace->getAlias(['name' => 'record_index_alias'])->willReturn(
            ['record_index_name' => 'record_index_alias'],
            ['migrated_index_name' => 'record_index_alias']
        );

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:25-0700');
        $clock->now()->willReturn($firstDatetime, $datetimeAfterFirstIndexation, $datetimeAfterSwitch);

        $initialMigratedIndexSetting = ['index' => ['refresh_interval' => '6']];
        $migratedIndexSettingDuringMigration = ['index' => ['refresh_interval' => '-1']];

        $indicesNamespace->getSettings(['index' => 'migrated_index_name'])->willReturn(
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
            ['migrated_index_name' => ['settings' => $migratedIndexSettingDuringMigration]],
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
        );

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $migratedIndexSettingDuringMigration])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $initialMigratedIndexSetting])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        // First indexation
        $getRecordIdentifiersUpdatedAfterDatetime->nextBatch($currentIndexRecordClient, $firstDatetime, 10)->willReturn(
            [
                [
                    'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
                    'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34',
                    'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
                ]
            ]
        );

        $normalizedRecords = [
            ['code' => 'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'],
            ['code' => 'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'],
            ['code' => 'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2']
        ];

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'))
            ->willReturn($normalizedRecords[0]);

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'))
            ->willReturn($normalizedRecords[1]);

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2'))
            ->willReturn($normalizedRecords[2]);

        $migratedIndexRecordClient->bulkIndexes($normalizedRecords, 'identifier', Refresh::disable());

        // Second indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $datetimeAfterFirstIndexation->modify('- 1msec'), 10)
            ->willReturn([]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'record_index_alias', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'record_index_alias', 'index' => 'record_index_name'],
                    ],
                    [
                        'add' => ['alias' => 'migrated_index_alias', 'index' => 'record_index_name']
                    ],
                    [
                        'remove' => ['alias' => 'migrated_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => true]);

        // Indexation after index swap
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($migratedIndexRecordClient, $datetimeAfterSwitch->modify('- 1msec'), 10)
            ->willReturn([]);

        $indicesNamespace->delete(['index' => 'record_index_name'])->willReturn(['acknowledged' => true]);

        $this->execute($migratedIndexRecordClient, 'migrated_index_alias', 'migrated_index_name');
    }

    public function it_reindex_records_updated_during_the_indexation(
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        IndicesNamespace $indicesNamespace,
        Client $currentIndexRecordClient,
        Client $migratedIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime
    ) {
        $indicesNamespace->getAlias(['name' => 'record_index_alias'])->willReturn(
            ['record_index_name' => 'record_index_alias'],
            ['migrated_index_name' => 'record_index_alias']
        );

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

        $initialMigratedIndexSetting = ['index' => ['refresh_interval' => '6']];
        $migratedIndexSettingDuringMigration = ['index' => ['refresh_interval' => '-1']];

        $indicesNamespace->getSettings(['index' => 'migrated_index_name'])->willReturn(
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
            ['migrated_index_name' => ['settings' => $migratedIndexSettingDuringMigration]],
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
        );

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $migratedIndexSettingDuringMigration])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $initialMigratedIndexSetting])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        // First indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $firstDatetime, 10)
            ->willReturn([
                [
                    'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
                    'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34',
                    'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
                ]
            ]);

        $normalizedRecords = [
            ['code' => 'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'],
            ['code' => 'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'],
        ];

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'))
            ->willReturn($normalizedRecords[0]);

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'))
            ->willReturn($normalizedRecords[1]);

        $migratedIndexRecordClient->bulkIndexes($normalizedRecords, 'identifier', Refresh::disable());

        // Second indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $datetimeAfterFirstIndexation->modify('- 1msec'), 10)
            ->willReturn([['designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2']]);

        $starckNormalized = ['code' => 'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2'];
        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2'))
            ->willReturn($starckNormalized);

        $migratedIndexRecordClient->bulkIndexes([$starckNormalized], 'identifier', Refresh::disable());

        // Third indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $datetimeAfterSecondIndexation->modify('- 1msec'), 10)
            ->willReturn([]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'record_index_alias', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'record_index_alias', 'index' => 'record_index_name'],
                    ],
                    [
                        'add' => ['alias' => 'migrated_index_alias', 'index' => 'record_index_name']
                    ],
                    [
                        'remove' => ['alias' => 'migrated_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => true]);

        // Indexation after index swap
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($migratedIndexRecordClient, $datetimeAfterSwitch->modify('- 1msec'), 10)
            ->willReturn([]);

        $indicesNamespace->delete(['index' => 'record_index_name'])->willReturn(['acknowledged' => true]);

        $this->execute($migratedIndexRecordClient, 'migrated_index_alias', 'migrated_index_name');
    }

    public function it_reindex_records_updated_between_indexation_and_swap(
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        IndicesNamespace $indicesNamespace,
        Client $currentIndexRecordClient,
        Client $migratedIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime
    ) {
        $indicesNamespace->getAlias(['name' => 'record_index_alias'])->willReturn(
            ['record_index_name' => 'record_index_alias'],
            ['migrated_index_name' => 'record_index_alias']
        );

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );

        $initialMigratedIndexSetting = ['index' => ['refresh_interval' => '6']];
        $migratedIndexSettingDuringMigration = ['index' => ['refresh_interval' => '-1']];

        $indicesNamespace->getSettings(['index' => 'migrated_index_name'])->willReturn(
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
            ['migrated_index_name' => ['settings' => $migratedIndexSettingDuringMigration]],
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
        );

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $migratedIndexSettingDuringMigration])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $initialMigratedIndexSetting])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        // First indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $firstDatetime, 10)
            ->willReturn([
                [
                    'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
                    'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34',
                ]
            ]);

        $normalizedRecords = [
            ['code' => 'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'],
            ['code' => 'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'],
        ];

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'))
            ->willReturn($normalizedRecords[0]);

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'))
            ->willReturn($normalizedRecords[1]);

        $migratedIndexRecordClient->bulkIndexes($normalizedRecords, 'identifier', Refresh::disable());

        // Second indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $datetimeAfterFirstIndexation->modify('- 1msec'), 10)
            ->willReturn([]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'record_index_alias', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'record_index_alias', 'index' => 'record_index_name'],
                    ],
                    [
                        'add' => ['alias' => 'migrated_index_alias', 'index' => 'record_index_name']
                    ],
                    [
                        'remove' => ['alias' => 'migrated_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => true]);

        // Indexation after index swap
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($migratedIndexRecordClient, $datetimeAfterSwitch->modify('- 1msec'), 10)
            ->willReturn([['designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2']]);

        $starckNormalized = ['code' => 'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2'];
        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2'))
            ->willReturn($starckNormalized);

        $currentIndexRecordClient->bulkIndexes([$starckNormalized], 'identifier', Refresh::disable());

        $indicesNamespace->delete(['index' => 'record_index_name'])->willReturn(['acknowledged' => true]);

        $this->execute($migratedIndexRecordClient, 'migrated_index_alias', 'migrated_index_name');
    }

    public function it_does_not_remove_index_while_swap_is_not_done(
        ClockInterface $clock,
        RecordNormalizerInterface $recordIndexationNormalizer,
        IndicesNamespace $indicesNamespace,
        Client $currentIndexRecordClient,
        Client $migratedIndexRecordClient,
        GetRecordIdentifiersUpdatedAfterDatetime $getRecordIdentifiersUpdatedAfterDatetime
    ) {
        $indicesNamespace->getAlias(['name' => 'record_index_alias'])->willReturn(
            ['record_index_name' => 'record_index_alias'],
            ['migrated_index_name' => 'record_index_alias']
        );

        $firstDatetime = new \DateTimeImmutable('@0');
        $datetimeAfterFirstIndexation = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $datetimeAfterSwitch = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:28-0700');
        $clock->now()->willReturn(
            $firstDatetime,
            $datetimeAfterFirstIndexation,
            $datetimeAfterSwitch
        );

        $initialMigratedIndexSetting = ['index' => ['refresh_interval' => '6']];
        $migratedIndexSettingDuringMigration = ['index' => ['refresh_interval' => '-1']];

        $indicesNamespace->getSettings(['index' => 'migrated_index_name'])->willReturn(
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
            ['migrated_index_name' => ['settings' => $migratedIndexSettingDuringMigration]],
            ['migrated_index_name' => ['settings' => $initialMigratedIndexSetting]],
        );

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $migratedIndexSettingDuringMigration])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        $indicesNamespace
            ->putSettings(['index' => 'migrated_index_name', 'body' => $initialMigratedIndexSetting])
            ->shouldBeCalledTimes(1)
            ->willReturn(['acknowledged' => true]);

        // First indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $firstDatetime, 10)
            ->willReturn([
                [
                    'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
                ]
            ]);

        $normalizedRecords = [
            ['code' => 'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'],
            ['code' => 'designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'],
        ];

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd'))
            ->willReturn($normalizedRecords[0]);

        $recordIndexationNormalizer
            ->normalizeRecord(RecordIdentifier::fromString('designer_coco_34aee120-fa95-4ff2-8439-bea116120e34'))
            ->willReturn($normalizedRecords[1]);

        $migratedIndexRecordClient->bulkIndexes($normalizedRecords, 'identifier', Refresh::disable());

        // Second indexation
        $getRecordIdentifiersUpdatedAfterDatetime
            ->nextBatch($currentIndexRecordClient, $datetimeAfterFirstIndexation->modify('- 1msec'), 10)
            ->willReturn([]);

        $indicesNamespace->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => ['alias' => 'record_index_alias', 'index' => 'migrated_index_name'],
                    ],
                    [
                        'remove' => ['alias' => 'record_index_alias', 'index' => 'record_index_name'],
                    ],
                    [
                        'add' => ['alias' => 'migrated_index_alias', 'index' => 'record_index_name']
                    ],
                    [
                        'remove' => ['alias' => 'migrated_index_alias', 'index' => 'migrated_index_name']
                    ],
                ]
            ]
        ])->willReturn(['acknowledged' => false]);

        $indicesNamespace->delete(['index' => 'record_index_name'])->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Index switch is not acknowledged'))
            ->during('execute', [$migratedIndexRecordClient, 'migrated_index_alias', 'migrated_index_name']);
    }
}
