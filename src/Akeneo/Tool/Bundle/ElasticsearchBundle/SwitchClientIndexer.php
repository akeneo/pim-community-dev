<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Platform\VersionProviderInterface;
use Webmozart\Assert\Assert;

/**
 * The purpose of this class is to provide index methods that impact current index + the next index.
 * Typically during an ES re-indexation there is an "old" index and a "new" one. When users alter documents we
 * must index on the both indexes.
 * It uses the actual PIM version to determine the new index name.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SwitchClientIndexer implements ClientIndexerInterface
{
    public const ONLY_CURRENT_INDEX_MODE = 'only_current_index';
    public const BOTH_INDEXES_MODE = 'both_indexes';
    public const ONLY_NEXT_INDEX_MODE = 'only_next_index';

    private Client $client;
    private VersionProviderInterface $versionProvider;
    private string $mode;
    private ?Client $clientForNextIndex = null;

    public function __construct(
        Client $client,
        VersionProviderInterface $versionProvider,
        string $mode = 'only_current_index'
    ) {
        $this->client = $client;
        $this->versionProvider = $versionProvider;
        $this->setMode($mode);
    }

    public function setMode(string $mode): void
    {
        Assert::oneOf($mode, [
            static::ONLY_CURRENT_INDEX_MODE,
            static::BOTH_INDEXES_MODE,
            static::ONLY_NEXT_INDEX_MODE,
        ]);

        $this->mode = $mode;
    }

    /**
     * {@inheritDoc}
     */
    public function index(string $id, array $body, Refresh $refresh = null): array
    {
        $results = [];
        foreach ($this->getClientsToImpact() as $client) {
            $results[] = $client->index($id, $body, $refresh);
        }

        Assert::notEmpty($results, 'No indexation is performed');

        return $results[0];
    }

    /**
     * {@inheritDoc}
     */
    public function bulkIndexes(array $documents, string $keyAsId = null, Refresh $refresh = null): array
    {
        $results = [];
        foreach ($this->getClientsToImpact() as $client) {
            $results[] = $client->bulkIndexes($documents, $keyAsId, $refresh);
        }

        Assert::notEmpty($results, 'No indexation is performed');

        return $results[0];
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByQuery(array $query): void
    {
        foreach ($this->getClientsToImpact() as $client) {
            $results[] = $client->deleteByQuery($query);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex(): array
    {
        $results = [];
        foreach ($this->getClientsToImpact() as $client) {
            $results[] = $client->refreshIndex();
        }

        Assert::notEmpty($results, 'No refresh is performed');

        return $results[0];
    }

    private function getClientsToImpact(): array
    {
        $clients = [];
        if (in_array($this->mode, [static::BOTH_INDEXES_MODE, static::ONLY_CURRENT_INDEX_MODE])) {
            $clients[] = $this->client;
        }

        if (in_array($this->mode, [static::BOTH_INDEXES_MODE, static::ONLY_NEXT_INDEX_MODE])) {
            $clients[] = $this->getClientForNextIndex();
        }

        return array_filter($clients);
    }

    private function getClientForNextIndex(): ?Client
    {
        if ('Serenity' !== $this->versionProvider->getEdition() || !$this->versionProvider->isSaaSVersion()) {
            return null;
        }

        if (null === $this->clientForNextIndex) {
            $this->clientForNextIndex = new Client(
                $this->client->getBuilder(),
                $this->client->getConfigurationLoader(),
                $this->client->getHosts(),
                $this->getNextIndexName(),
                $this->client->getIdPrefix(),
            );
        }

        return $this->clientForNextIndex;
    }

    public function getNextIndexName(): string
    {
        return sprintf("%s_%s", $this->client->getIndexName(), $this->versionProvider->getMinorVersion());
    }
}
