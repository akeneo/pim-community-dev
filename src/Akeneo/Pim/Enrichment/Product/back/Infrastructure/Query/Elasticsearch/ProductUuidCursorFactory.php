<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Product\Application\PQB\ProductUuidCursor;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductUuidCursorFactory implements CursorFactoryInterface
{
    public function __construct(private Client $client, private int $pageSize)
    {
    }

    public function createCursor($queryBuilder, array $options = [])
    {
        $pageSize = !isset($options['page_size']) ? $this->pageSize : $options['page_size'];
        $queryBuilder['_source'] = \array_merge($queryBuilder['_source'], ['document_type']);

        $fetcher = new ElasticsearchProductUuidQueryFetcher($this->client, $queryBuilder,$pageSize);

        return ProductUuidCursor::createFromFetcher($fetcher);
    }
}
