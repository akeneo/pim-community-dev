<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

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

    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $entityClassName;

    /** @var string */
    protected $cursorClassName;

    /** @var int */
    protected $pageSize;

    /** @var string */
    protected $indexType;

    /**
     * @param Client        $searchEngine
     * @param ObjectManager $om
     * @param string        $entityClassName
     * @param string        $cursorClassName
     * @param int           $pageSize
     * @param string        $indexType
     */
    public function __construct(
        Client $searchEngine,
        ObjectManager $om,
        $entityClassName,
        $cursorClassName,
        $pageSize,
        $indexType
    ) {
        $this->searchEngine = $searchEngine;
        $this->om = $om;
        $this->entityClassName = $entityClassName;
        $this->cursorClassName = $cursorClassName;
        $this->pageSize = $pageSize;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, array $options = [])
    {
        $repository = $this->om->getRepository($this->entityClassName);
        if (!$repository instanceof CursorableRepositoryInterface) {
            throw InvalidObjectException::objectExpected($this->entityClassName, CursorableRepositoryInterface::class);
        }

        $pageSize = !isset($options['page_size']) ? $this->pageSize : $options['page_size'];

        return new $this->cursorClassName($this->searchEngine, $repository, $queryBuilder, $this->indexType, $pageSize);
    }
}
