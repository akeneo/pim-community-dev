<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorFactoryInterface;

/**
 * Class CursorFactory to instantiate cursor to iterate entities
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorFactory implements CursorFactoryInterface
{
    /** @var string */
    protected $cursorClass = null;

    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $entityClass;

    /** @var int */
    protected $pageSize;

    /**
     * @param string        $cursorClass class name implementation
     * @param EntityManager $entityManager
     * @param int           $pageSize
     */
    public function __construct(
        $cursorClass,
        EntityManager $entityManager,
        $pageSize
    ) {
        $this->cursorClass = $cursorClass;
        $this->entityManager = $entityManager;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, $pageSize=null)
    {
        if ($pageSize==null) {
            $pageSize = $this->pageSize;
        }
        return new $this->cursorClass($queryBuilder, $this->entityManager, $pageSize);
    }
}
