<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

class CursorFactory implements CursorFactoryInterface
{
    public function __construct(
        protected Client $searchEngine,
        private PublishedProductRepositoryInterface $publishedProductRepository,
        protected int $pageSize
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = []): CursorInterface
    {
        $pageSize = !isset($options['page_size']) ? $this->pageSize : $options['page_size'];

        $queryBuilder['_source'] = array_merge($queryBuilder['_source'], ['document_type', 'id']);

        return new Cursor(
            $this->searchEngine,
            $this->publishedProductRepository,
            $queryBuilder,
            $pageSize
        );
    }
}
