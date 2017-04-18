<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;

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
    public function createCursor($queryBuilder, array $options = [])
    {
        if (!isset($options['page_size'])) {
            $options = $this->pageSize;
        }

        return new $this->cursorClass($queryBuilder, $options);
    }
}
