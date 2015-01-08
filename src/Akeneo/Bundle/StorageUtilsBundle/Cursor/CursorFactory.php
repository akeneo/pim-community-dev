<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

/**
 * Class CursorFactory to instantiate cursor to iterate product
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CursorFactory implements CursorFactoryInterface
{
    /** @var string */
    private $cursorClass = null;

    /**
     * @param string $productCursorClass class name implementation
     */
    public function __construct(
        $productCursorClass
    ) {
        $this->cursorClass = $productCursorClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder)
    {
        return new $this->cursorClass($queryBuilder);
    }
}
