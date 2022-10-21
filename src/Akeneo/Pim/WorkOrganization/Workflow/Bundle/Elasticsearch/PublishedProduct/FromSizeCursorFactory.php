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
class FromSizeCursorFactory implements CursorFactoryInterface
{
    public function __construct(
        private Client $searchEngine,
        private PublishedProductRepositoryInterface $publishedProductRepository,
        private int $pageSize
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = []): CursorInterface
    {
        $options = $this->resolveOptions($options);

        $queryBuilder['_source'] = array_merge($queryBuilder['_source'], ['id', 'identifier']);

        return new FromSizeCursor(
            $this->searchEngine,
            $this->publishedProductRepository,
            $queryBuilder,
            $options['page_size'],
            $options['limit'],
            $options['from']
        );
    }

    protected function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(
            [
                'page_size',
                'limit',
                'from',
            ]
        );
        $resolver->setDefaults(
            [
                'page_size' => $this->pageSize,
                'from' => 0
            ]
        );
        $resolver->setAllowedTypes('page_size', 'int');
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setAllowedTypes('from', 'int');

        $options = $resolver->resolve($options);

        return $options;
    }
}
