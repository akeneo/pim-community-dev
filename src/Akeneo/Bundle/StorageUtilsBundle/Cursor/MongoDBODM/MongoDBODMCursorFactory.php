<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor\MongoDBODM;

use Akeneo\Bundle\StorageUtilsBundle\Cursor\CursorFactoryInterface;

/**
 * Class MongoDBODMCursorFactory to instantiate cursor to iterate entities
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MongoDBODMCursorFactory implements CursorFactoryInterface
{
    /** @var string */
    private $cursorClass = null;

    /** @type int */
    private $batchSize;

    /**
     * @param string $productCursorClass class name implementation
     * @param int $batchSize
     */
    public function __construct(
        $productCursorClass,
        $batchSize=null
    ) {
        $this->cursorClass = $productCursorClass;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createCursor($queryBuilder)
    {
        return new $this->cursorClass($queryBuilder, $this->batchSize);
    }
}
