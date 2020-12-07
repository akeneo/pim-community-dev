<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MultipleClientIndexer implements ClientIndexerInterface
{
    /** @var IndexerInterface[] */
    private array $indexers = [];

    public function __construct(array $indexers)
    {
        Assert::allIsInstanceOf($indexers, ClientIndexerInterface::class);
        Assert::notEmpty($indexers);
        $this->indexers = $indexers;
    }

    /**
     * {@inheritDoc}
     */
    public function index(string $id, array $body, Refresh $refresh = null): array
    {
        $result = [];
        foreach ($this->indexers as $indexer) {
            $results[] = $indexer->index($id, $body, $refresh);
        }

        return $results[0];
    }

    /**
     * {@inheritDoc}
     */
    public function bulkIndexes(array $documents, string $keyAsId = null, Refresh $refresh = null): array
    {
        $results = [];
        foreach ($this->indexers as $indexer) {
            $results[] = $indexer->bulkIndexes($documents, $keyAsId, $refresh);
        }

        return $results[0];
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByQuery(array $query): void
    {
        foreach ($this->indexers as $indexer) {
            $indexer->deleteByQuery($query);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex(): array
    {
        $results = [];
        foreach ($this->indexers as $indexer) {
            $results[] = $indexer->refreshIndex();
        }

        return $results[0];
    }
}
