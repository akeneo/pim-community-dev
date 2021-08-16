<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Akeneo\Tool\Component\Elasticsearch\ClockInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Webmozart\Assert\Assert;

/**
 * This class is meant to update an index mapping without downtime it's used for SASS clients
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/reindex-upgrade-inplace.html
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class UpdateIndexMappingWithoutDowntime
{
    private ClockInterface $clock;
    private Client $client;

    public function __construct(ClockInterface $clock, ClientBuilder $clientBuilder, array $hosts)
    {
        $this->clock = $clock;
        $this->client = $clientBuilder->setHosts($hosts)->build();
    }

    public function execute(
        string $indexAliasToMigrate,
        string $indexAliasMigrated,
        string $indexNameMigrated,
        IndexConfiguration $indexConfiguration,
        \Closure $findUpdatedDocumentQuery
    ): void {
        $indexNameToMigrate = $this->getIndexNameFromAlias($indexAliasToMigrate);
        $this->createIndexWithNewConfiguration($indexNameMigrated, $indexAliasMigrated, $indexConfiguration);
        $lastReferenceDatetime = $this->moveAllDocuments(
            $indexAliasToMigrate,
            $indexAliasMigrated,
            $findUpdatedDocumentQuery
        );

        $this->resetIndexSettings($indexNameMigrated, $indexNameToMigrate);
        $this->switchIndexAliasToNewIndex(
            $indexAliasToMigrate,
            $indexNameToMigrate,
            $indexAliasMigrated,
            $indexNameMigrated
        );

        $this->reindexDocumentUpdatedAfterDatetime(
            $indexAliasMigrated,
            $indexAliasToMigrate,
            $lastReferenceDatetime,
            $findUpdatedDocumentQuery
        );

        $this->removeOldIndex($indexNameToMigrate);
    }

    private function createIndexWithNewConfiguration(
        string $indexName,
        string $indexAlias,
        IndexConfiguration $indexConfiguration
    ): void {
        $indicesClient = $this->client->indices();
        $body = $indexConfiguration->buildAggregated();

        $body['settings']['index']['number_of_replicas'] = 0;
        $body['settings']['index']['refresh_interval'] = -1;
        $body['aliases'] = [$indexAlias => (object) []];

        $indexCreationResponse = $indicesClient->create([
            'index' => $indexName,
            'body' => $body
        ]);

        Assert::true($indexCreationResponse['acknowledged']);
    }

    private function moveAllDocuments(
        string $sourceIndexAliasName,
        string $targetIndexAliasName,
        \Closure $findUpdatedDocumentQuery
    ): \DateTimeImmutable {
        $lastReferenceDatetime = $this->clock->now()->setTimestamp(0);
        do {
            $nextUpdateTime = $this->clock->now()->modify('- 1second');
            $indexedRecords = $this->reindexDocumentUpdatedAfterDatetime(
                $sourceIndexAliasName,
                $targetIndexAliasName,
                $lastReferenceDatetime,
                $findUpdatedDocumentQuery
            );

            $lastReferenceDatetime = $nextUpdateTime;
        } while ($indexedRecords > 0);

        return $lastReferenceDatetime;
    }

    private function reindexDocumentUpdatedAfterDatetime(
        string $sourceIndexAliasName,
        string $targetIndexAliasName,
        \DateTimeImmutable $referenceDatetime,
        \Closure $findUpdatedDocumentQuery
    ): int {
        $reindexResponse = $this->client->reindex([
            'wait_for_completion' => true,
            'body' => [
                "source" => [
                    "index" => $sourceIndexAliasName,
                    "query" => $findUpdatedDocumentQuery($referenceDatetime),
                ],
                "dest" => [
                    "index" => $targetIndexAliasName,
                ]
            ]
        ]);

        return $reindexResponse["total"];
    }

    private function switchIndexAliasToNewIndex(
        string $oldIndexAlias,
        string $oldIndexName,
        string $newIndexAlias,
        string $newIndexName
    ): void {
        $response = $this->client->indices()->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'add' => [
                            'alias' => $oldIndexAlias,
                            'index' => $newIndexName,
                        ],
                    ],
                    [
                        'remove' => [
                            'alias' => $oldIndexAlias,
                            'index' => $oldIndexName,
                        ],
                    ],
                    [
                        'add' => [
                            'alias' => $newIndexAlias,
                            'index' => $oldIndexName,
                        ]
                    ],
                    [
                        'remove' => [
                            'alias' => $newIndexAlias,
                            'index' => $newIndexName,
                        ]
                    ],
                ]
            ]
        ]);

        Assert::true($response['acknowledged'], 'Index switch is not acknowledged');

        $indexNameAfterMigration = $this->getIndexNameFromAlias($oldIndexAlias);
        Assert::notEq($oldIndexName, $indexNameAfterMigration, 'The index name used by the alias have not changed');
    }

    private function resetIndexSettings(
        string $indexName,
        string $oldIndexName
    ): void {
        $indicesClient = $this->client->indices();
        $settingsResponse = $indicesClient->getSettings(['index' => $oldIndexName]);
        $oldIndexSettings = $settingsResponse[$oldIndexName]['settings'];

        $response = $indicesClient->putSettings([
            'index' => $indexName,
            'body' => [
                'index' => [
                    'refresh_interval' => $oldIndexSettings['index']['refresh_interval'] ?? null,
                    'number_of_replicas' => $oldIndexSettings['index']['number_of_replicas'] ?? 1,
                ]
            ]
        ]);

        Assert::true($response['acknowledged'], 'Reset index settings is not acknowledged');
    }

    private function removeOldIndex(string $indexName): void
    {
        $response = $this->client->indices()->delete(['index' => $indexName]);

        Assert::true($response['acknowledged'], 'Remove index is not acknowledged');
    }

    private function getIndexNameFromAlias(string $indexAlias): string
    {
        $aliases = $this->client->indices()->getAlias(['name' => $indexAlias]);
        $indexNames = \array_keys($aliases);

        Assert::keyExists($indexNames, 0, 'No index name found from $indexAlias index alias');
        Assert::count($indexNames, 1, 'Cannot migrated an index alias with more than one index');

        return $indexNames[0];
    }
}
