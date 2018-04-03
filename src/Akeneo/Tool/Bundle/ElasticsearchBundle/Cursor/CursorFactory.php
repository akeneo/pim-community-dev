<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Cursor factory to instantiate an elasticsearch cursor
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorFactory implements CursorFactoryInterface
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
     * @param CursorableRepositoryInterface $repository
     * @param string                        $cursorClassName
     * @param int                           $pageSize
     * @param string                        $indexType
     */
    public function __construct(
        Client $searchEngine,
        CursorableRepositoryInterface $repository,
        $cursorClassName,
        $pageSize,
        $indexType
    ) {
        $this->searchEngine = $searchEngine;
        $this->cursorableRepository = $repository;
        $this->cursorClassName = $cursorClassName;
        $this->pageSize = $pageSize;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $pageSize = !isset($options['page_size']) ? $this->pageSize : $options['page_size'];

        return new $this->cursorClassName(
            $this->searchEngine,
            $this->cursorableRepository,
            $queryBuilder,
            $this->indexType,
            $pageSize
        );
    }
}
