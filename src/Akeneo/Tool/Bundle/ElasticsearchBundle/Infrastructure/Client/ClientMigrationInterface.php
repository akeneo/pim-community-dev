<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client;

interface ClientMigrationInterface
{
    public function getIndexNameFromAlias(string $indexAlias): array;
    public function aliasExist(string $indexAlias): bool;
    public function createAlias(string $indexAlias, string $indexName): void;
    public function reindex(string $sourceIndexAlias, string $targetIndexAlias, array $query);
    public function removeIndex(string $indexName): void;
    public function getIndexSettings(string $indexName): array;
    public function putIndexSetting(string $indexName, array $indexSettings);
    public function switchIndexAlias(string $oldIndexAlias, string $oldIndexName, string $newIndexAlias, string $newIndexName): void;
    public function createIndex(string $indexName, array $body): void;
    public function renameAlias(string $oldIndexAlias, string $newIndexAlias, string $indexName): void;

    public function refreshIndex(string $indexName);
}
