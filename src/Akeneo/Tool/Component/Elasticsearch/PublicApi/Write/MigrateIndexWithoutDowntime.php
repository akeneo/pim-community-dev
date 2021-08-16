<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Elasticsearch\PublicApi\Write;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;

class MigrateIndexWithoutDowntime
{
    private string $indexAlias;
    private \Closure $findUpdatedDocumentQuery;
    private IndexConfiguration $indexConfiguration;

    public function __construct(
        string $indexAlias,
        IndexConfiguration $indexConfiguration,
        \Closure $findUpdatedDocumentQuery
    ) {
        $this->indexAlias = $indexAlias;
        $this->indexConfiguration = $indexConfiguration;
        $this->findUpdatedDocumentQuery = $findUpdatedDocumentQuery;
    }

    public function getIndexAlias(): string
    {
        return $this->indexAlias;
    }

    public function getIndexConfiguration(): IndexConfiguration
    {
        return $this->indexConfiguration;
    }

    public function getFindUpdatedDocumentQuery(): \Closure
    {
        return $this->findUpdatedDocumentQuery;
    }
}
