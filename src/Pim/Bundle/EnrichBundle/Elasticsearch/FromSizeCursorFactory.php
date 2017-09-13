<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    private $searchEngine;

    /** @var int */
    private $pageSize;

    /** @var string */
    private $indexType;

    /** @var CursorableRepositoryInterface */
    private $productRepository;

    /** @var CursorableRepositoryInterface */
    private $productModelRepository;

    /**
     * @param Client                        $searchEngine
     * @param CursorableRepositoryInterface $productRepository
     * @param CursorableRepositoryInterface $productModelRepository
     * @param int                           $pageSize
     * @param string                        $indexType
     */
    public function __construct(
        Client $searchEngine,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        $pageSize,
        $indexType
    ) {
        $this->searchEngine = $searchEngine;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->pageSize = $pageSize;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $options = $this->resolveOptions($options);

        $queryBuilder['_source'] = array_merge($queryBuilder['_source'], ['document_type']);

        return new FromSizeCursor(
            $this->searchEngine,
            $this->productRepository,
            $this->productModelRepository,
            $queryBuilder,
            $this->indexType,
            $options['page_size'],
            $options['limit'],
            $options['from']
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
