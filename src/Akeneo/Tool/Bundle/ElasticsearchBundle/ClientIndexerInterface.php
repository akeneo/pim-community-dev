<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ClientIndexerInterface
{
    /**
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_index_a_document}
     */
    public function index(string $id, array $body, Refresh $refresh = null): array;

    /**
     * @throws MissingIdentifierException
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_indexing_documents.html#_bulk_indexing}
     */
    public function bulkIndexes(array $documents, string $keyAsId = null, Refresh $refresh = null): array;

    /**
     * @param array $query an array containing a query compatible with https://www.elastic.co/guide/en/elasticsearch/reference/5.5/docs-delete-by-query.html
     */
    public function deleteByQuery(array $query): void;

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html}
     */
    public function refreshIndex(): array;
}
