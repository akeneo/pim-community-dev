<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Cursor factory to instantiate an elasticsearch bounded cursor
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAfterSizeCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    protected $searchEngine;

    /** @var string */
    protected $cursorClassName;

    /** @var int */
    protected $pageSize;

    /** @var string */
    protected $indexType;

    /** @var CursorableRepositoryInterface */
    protected $cursorableRepository;

    /**
     * @param Client                        $searchEngine
     * @param CursorableRepositoryInterface $cursorableRepository
     * @param string                        $cursorClassName
     * @param int                           $pageSize
     * @param string                        $indexType
     */
    public function __construct(
        Client $searchEngine,
        CursorableRepositoryInterface $cursorableRepository,
        $cursorClassName,
        $pageSize,
        $indexType
    ) {
        $this->searchEngine = $searchEngine;
        $this->cursorClassName = $cursorClassName;
        $this->pageSize = $pageSize;
        $this->indexType = $indexType;
        $this->cursorableRepository = $cursorableRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $options = $this->resolveOptions($options);

        return new $this->cursorClassName(
            $this->searchEngine,
            $this->cursorableRepository,
            $queryBuilder,
            $options['search_after'],
            $this->indexType,
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
