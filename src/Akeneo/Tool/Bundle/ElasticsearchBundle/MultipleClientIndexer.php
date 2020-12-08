<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Platform\VersionProviderInterface;

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
final class MultipleClientIndexer implements ClientIndexerInterface
{
    private Client $client;
    private VersionProviderInterface $versionProvider;
    private ?Client $clientForNextIndex = null;

    public function __construct(Client $client, VersionProviderInterface $versionProvider)
    {
        $this->client = $client;
        $this->versionProvider = $versionProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function index(string $id, array $body, Refresh $refresh = null): array
    {
        $result = $this->client->index($id, $body, $refresh);
        $client = $this->getClientForNextIndex();
        if (null !== $client) {
            $client->index($id, $body, $refresh);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function bulkIndexes(array $documents, string $keyAsId = null, Refresh $refresh = null): array
    {
        $result = $this->client->bulkIndexes($documents, $keyAsId, $refresh);
        $client = $this->getClientForNextIndex();
        if (null !== $client) {
            $client->bulkIndexes($documents, $keyAsId, $refresh);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByQuery(array $query): void
    {
        $this->client->deleteByQuery($query);
        $client = $this->getClientForNextIndex();
        if (null !== $client) {
            $client->deleteByQuery($query);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex(): array
    {
        $result = $this->client->refreshIndex();
        $client = $this->getClientForNextIndex();
        if (null !== $client) {
            $client->refreshIndex();
        }

        return $result;
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
