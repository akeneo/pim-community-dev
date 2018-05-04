<?php

namespace Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\Exception\UnsupportedItemTypeException;

/**
 * A buffer is an object able to read and write items. It is a simple iterator with an additional write() method.
 * The behavior of the buffer (FIFO / LIFO) and how the items are stocked must be defined by the implementation.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface BufferInterface extends \Iterator
{
    /**
     * @param mixed $item                   The item to write in the buffer
     * @param array $options                The options required by the buffer
     *
     * @throws UnsupportedItemTypeException If the buffer implementation does not support item of this type
     */
    public function write($item, array $options = []);
}
