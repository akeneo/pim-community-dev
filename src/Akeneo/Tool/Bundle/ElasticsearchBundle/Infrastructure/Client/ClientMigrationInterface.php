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

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client;

interface ClientMigrationInterface
{
    public function getIndexNameFromAlias(string $indexAlias): array;
    public function reindex(string $sourceIndexAlias, string $targetIndexAlias, array $query);
    public function removeIndex(string $indexName): void;
    public function getIndexSettings(string $indexName): array;
    public function putIndexSetting(string $indexName, array $indexSettings);
    public function switchIndexAlias(string $oldIndexAlias, string $oldIndexName, string $newIndexAlias, string $newIndexName): void;
    public function createIndex(string $indexName, array $body): void;
}
