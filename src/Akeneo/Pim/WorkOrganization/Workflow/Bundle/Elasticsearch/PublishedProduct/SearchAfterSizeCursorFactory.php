<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class SearchAfterSizeCursorFactory implements CursorFactoryInterface
{
    public function __construct(
        protected Client $searchEngine,
        protected PublishedProductRepositoryInterface $publishedProductRepository,
        protected int $pageSize
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = []): CursorInterface
    {
        $options = $this->resolveOptions($options);

        return new SearchAfterSizeCursor(
            $this->searchEngine,
            $this->publishedProductRepository,
            $queryBuilder,
            $options['search_after'],
            $options['page_size'],
            $options['limit'],
            $options['search_after_unique_key']
        );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(
            [
                'page_size',
                'search_after',
                'search_after_unique_key',
                'limit'
            ]
        );
        $resolver->setDefaults(
            [
                'page_size' => $this->pageSize,
                'search_after' => [],
                'search_after_unique_key' => null
            ]
        );
        $resolver->setAllowedTypes('page_size', 'int');
        $resolver->setAllowedTypes('search_after', 'array');
        $resolver->setAllowedTypes('search_after_unique_key', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');

        $options = $resolver->resolve($options);

        return $options;
    }
}
