<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Elasticsearch\PublicApi\Read;

interface IndexMigrationIsDoneInterface
{
    public function byIndexAliasAndHash(string $indexAlias, string $hash): bool;
}
