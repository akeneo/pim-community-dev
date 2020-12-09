<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\ClientBuilder;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DualIndexationClient extends Client
{
    private Client $temporaryClient;

    public function __construct(
        ClientBuilder $builder,
        Loader $configurationLoader,
        array $hosts,
        string $aliasName,
        string $idPrefix = '',
        Client $temporaryClient
    ) {
        parent::__construct($builder, $configurationLoader, $hosts, $aliasName, $idPrefix);
        $this->temporaryClient = $temporaryClient;
    }

    /**
     * {@inheritDoc}
     */
    public function index(string $id, array $body, Refresh $refresh = null): array
    {
        $this->temporaryClient->index($id, $body, $refresh);

        return parent::index($id, $body, $refresh);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkIndexes(array $documents, string $keyAsId = null, Refresh $refresh = null): array
    {
        $this->temporaryClient->bulkIndexes($documents, $keyAsId, $refresh);

        return parent::bulkIndexes($documents, $keyAsId, $refresh);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByQuery(array $query): void
    {
        parent::deleteByQuery($query);
        $this->temporaryClient->deleteByQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex(): array
    {
        $this->temporaryClient->refreshIndex();

        return parent::refreshIndex();
    }
}
