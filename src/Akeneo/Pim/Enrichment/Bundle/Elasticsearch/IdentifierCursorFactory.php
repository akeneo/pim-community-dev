<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Cursor factory to instantiate an elasticsearch cursor.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierCursorFactory implements CursorFactoryInterface
{
    public function __construct(private Client $searchEngine, private int $pageSize)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = []): CursorInterface
    {
        $pageSize = $options['page_size'] ?? $this->pageSize;
        $queryBuilder['_source'] = ['identifier', 'document_type', 'id'];

        return new IdentifierCursor($this->searchEngine, $queryBuilder, $pageSize);
    }
}
