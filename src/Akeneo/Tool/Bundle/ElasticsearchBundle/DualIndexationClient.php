<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\ClientBuilder;

/**
 * This class extends behavior of "write" methods in order to index on 2 both aliases in same time:
 *  - The alias specified by the $aliasName property
 *  - The alias associated to the $dualClient property
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DualIndexationClient extends Client
{
    private Client $dualClient;

    public function __construct(
        ClientBuilder $builder,
        Loader $configurationLoader,
        array $hosts,
        string $aliasName,
        string $idPrefix,
        Client $dualClient
    ) {
        parent::__construct($builder, $configurationLoader, $hosts, $aliasName, $idPrefix);
        $this->dualClient = $dualClient;
    }

    /**
     * {@inheritDoc}
     */
    public function index($id, array $body, Refresh $refresh = null): array
    {
        $this->dualClient->index($id, $body, $refresh);

        return parent::index($id, $body, $refresh);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkIndexes($documents, $keyAsId = null, Refresh $refresh = null): array
    {
        $this->dualClient->bulkIndexes($documents, $keyAsId, $refresh);

        return parent::bulkIndexes($documents, $keyAsId, $refresh);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByQuery(array $query): void
    {
        parent::deleteByQuery($query);
        $this->dualClient->deleteByQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex(): array
    {
        $this->dualClient->refreshIndex();

        return parent::refreshIndex();
    }
}
