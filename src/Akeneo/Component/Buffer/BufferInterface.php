<?php

namespace Akeneo\Component\Buffer;

use Akeneo\Component\Buffer\Exception\UnsupportedItemTypeException;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface BufferInterface extends \Iterator
{
    /**
     * @param mixed $item
     *
     * @throws UnsupportedItemTypeException
     */
    public function write($item);
}
