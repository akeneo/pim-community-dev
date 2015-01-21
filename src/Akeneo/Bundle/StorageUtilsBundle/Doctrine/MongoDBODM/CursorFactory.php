<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

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
    protected $cursorClass;

    /** @var int */
    protected $pageSize;

    /**
     * @param string $cursorClass class name implementation
     * @param int    $pageSize
     */
    public function __construct(
        $cursorClass,
        $pageSize = null
    ) {
        $this->cursorClass = $cursorClass;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder, $pageSize = null)
    {
        if ($pageSize == null) {
            $pageSize = $this->pageSize;
        }

        return new $this->cursorClass($queryBuilder, $pageSize);
    }
}
