<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client\ClientMigrationInterface;
use Akeneo\Tool\Component\Elasticsearch\ClockInterface;
use Webmozart\Assert\Assert;

/**
 * This class is meant to update an index mapping without downtime it's used for SASS clients
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/reindex-upgrade-inplace.html
 *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
final class UpdateIndexMappingWithoutDowntime
{
    public function __construct(
        private ClockInterface $clock,
        private ClientMigrationInterface $migrationClient
    ) {
    }

    public function execute(
        string $sourceIndexAlias,
        string $destinationIndexAlias,
        string $destinationIndexName,
        IndexConfiguration $indexConfiguration,
        \Closure $findUpdatedDocumentQuery
    ): void {
        $indexToMigrateDoesNotHaveAlias = $this->indexToMigrateDoesNotHaveAlias($sourceIndexAlias);
        if ($indexToMigrateDoesNotHaveAlias) {
            $sourceIndexAlias = $this->createMigrationSourceAlias($sourceIndexAlias);
        }

        $sourceIndexName = $this->getIndexNameFromAlias($sourceIndexAlias);

        $this->createDestinationIndex($destinationIndexName, $destinationIndexAlias, $indexConfiguration);
        $lastReferenceDatetime = $this->moveAllDocuments(
            $sourceIndexAlias,
            $destinationIndexAlias,
            $findUpdatedDocumentQuery
        );

        $this->resetIndexSettings($destinationIndexName, $sourceIndexName);
        $this->switchIndexAliasToNewIndex(
            $sourceIndexAlias,
            $sourceIndexName,
            $destinationIndexAlias,
            $destinationIndexName
        );

        $this->reindexDocumentUpdatedDuringSwitch(
            $sourceIndexAlias,
            $destinationIndexAlias,
            $lastReferenceDatetime,
            $findUpdatedDocumentQuery
        );

        $this->removeOldIndex($sourceIndexName);
        if ($indexToMigrateDoesNotHaveAlias) {
            $this->renameSourceAlias($sourceIndexAlias, $sourceIndexName, $destinationIndexName);
        }
    }

    private function createMigrationSourceAlias($sourceIndexAlias): string
    {
        $migrationAlias = sprintf('%s_migration_alias', $sourceIndexAlias);

        $this->migrationClient->createAlias($migrationAlias, $sourceIndexAlias);

        return $migrationAlias;
    }

    private function createDestinationIndex(
        string $indexName,
        string $indexAlias,
        IndexConfiguration $indexConfiguration
    ): void {
        $body = $indexConfiguration->buildAggregated();
        $body['settings']['index']['number_of_replicas'] = 0;
        $body['settings']['index']['refresh_interval'] = -1;
        $body['aliases'] = [$indexAlias => (object) []];

        $this->migrationClient->createIndex($indexName, $body);
    }

    private function moveAllDocuments(
        string $sourceIndexAlias,
        string $targetIndexAlias,
        \Closure $findUpdatedDocumentQuery
    ): \DateTimeImmutable {
        $lastReferenceDatetime = $this->clock->now()->setTimestamp(0);
        do {
            $nextUpdateTime = $this->clock->now()->modify('- 1second');
            $indexedRecords = $this->reindexDocumentUpdatedAfterDatetime(
                $sourceIndexAlias,
                $targetIndexAlias,
                $lastReferenceDatetime,
                $findUpdatedDocumentQuery
            );

            $lastReferenceDatetime = $nextUpdateTime;
        } while ($indexedRecords > 0);

        return $lastReferenceDatetime;
    }

    private function reindexDocumentUpdatedAfterDatetime(
        string $sourceIndexAlias,
        string $targetIndexAlias,
        \DateTimeImmutable $referenceDatetime,
        \Closure $findUpdatedDocumentQuery
    ): int {
        return $this->migrationClient->reindex(
            $sourceIndexAlias,
            $targetIndexAlias,
            $findUpdatedDocumentQuery($referenceDatetime)
        );
    }

    private function switchIndexAliasToNewIndex(
        string $oldIndexAlias,
        string $oldIndexName,
        string $newIndexAlias,
        string $newIndexName
    ): void {
        $this->migrationClient->switchIndexAlias($oldIndexAlias, $oldIndexName, $newIndexAlias, $newIndexName);

        $indexNameAfterMigration = $this->getIndexNameFromAlias($oldIndexAlias);
        Assert::notEq($oldIndexName, $indexNameAfterMigration, 'The index name used by the alias have not changed');
    }

    private function resetIndexSettings(
        string $indexName,
        string $oldIndexName
    ): void {
        $oldIndexSettings = $this->migrationClient->getIndexSettings($oldIndexName);

        $this->migrationClient->putIndexSetting($indexName, [
            'refresh_interval' => $oldIndexSettings['refresh_interval'] ?? null,
            'number_of_replicas' => $oldIndexSettings['number_of_replicas'] ?? 1,
        ]);

        $this->migrationClient->refreshIndex($indexName);
    }

    private function removeOldIndex(string $indexName): void
    {
        $this->migrationClient->removeIndex($indexName);
    }

    private function getIndexNameFromAlias(string $indexAlias): string
    {
        $indexNames = $this->migrationClient->getIndexNameFromAlias($indexAlias);

        Assert::keyExists($indexNames, 0, 'No index name found from $indexAlias index alias');
        Assert::count($indexNames, 1, 'Cannot migrated an index alias with more than one index');

        return $indexNames[0];
    }

    private function reindexDocumentUpdatedDuringSwitch(
        string $sourceIndexAlias,
        string $destinationIndexAlias,
        \DateTimeImmutable $lastReferenceDatetime,
        \Closure $findUpdatedDocumentQuery
    ): void {
        $this->reindexDocumentUpdatedAfterDatetime(
            $destinationIndexAlias,
            $sourceIndexAlias,
            $lastReferenceDatetime,
            $findUpdatedDocumentQuery
        );
    }

    private function indexToMigrateDoesNotHaveAlias(string $sourceIndexAlias): bool
    {
        return !$this->migrationClient->aliasExist($sourceIndexAlias);
    }

    private function renameSourceAlias(string $sourceIndexAlias, string $sourceIndexName, string $destinationIndexName): void
    {
        $this->migrationClient->renameAlias($sourceIndexAlias, $sourceIndexName, $destinationIndexName);
    }
}
