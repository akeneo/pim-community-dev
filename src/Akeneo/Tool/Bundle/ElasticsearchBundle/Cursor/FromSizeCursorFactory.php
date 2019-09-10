<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeCursorFactory implements CursorFactoryInterface
{
    /** @var Client */
    protected $searchEngine;

    /** @var string */
    protected $cursorClassName;

    /** @var int */
    protected $pageSize;

    /** @var CursorableRepositoryInterface */
    protected $cursorableRepository;

    /**
     * @param Client                        $searchEngine
     * @param CursorableRepositoryInterface $cursorableRepository
     * @param string                        $cursorClassName
     * @param int                           $pageSize
     */
    public function __construct(
        Client $searchEngine,
        CursorableRepositoryInterface $cursorableRepository,
        $cursorClassName,
        $pageSize
    ) {
        $this->searchEngine = $searchEngine;
        $this->cursorClassName = $cursorClassName;
        $this->pageSize = $pageSize;
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
