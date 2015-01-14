<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor\ORM;

use Doctrine\ORM\EntityManager;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorFactoryInterface;

/**
 * Class ORMCursorFactory to instantiate cursor to iterate entities
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMCursorFactory implements CursorFactoryInterface
{
    /** @type string */
    private $cursorClass = null;

    /** @type EntityManager */
    private $entityManager;

    /** @type string */
    private $entityClass;

    /** @type int */
    private $pageSize;

    /**
     * @param string        $productCursorClass class name implementation
     * @param EntityManager $entityManager
     * @param string        $entityClass
     * @param int           $pageSize
     */
    public function __construct(
        $productCursorClass,
        EntityManager $entityManager,
        $pageSize
    ) {
        $this->cursorClass = $productCursorClass;
        $this->entityManager = $entityManager;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder)
    {
        return new $this->cursorClass($queryBuilder, $this->entityManager, $this->pageSize);
    }
}
