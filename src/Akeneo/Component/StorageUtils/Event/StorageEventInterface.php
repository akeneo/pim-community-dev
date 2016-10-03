<?php

namespace Akeneo\Component\StorageUtils\Event;

/**
 * Storage event interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Grégory Planchat <gregory@kiboko.fr>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StorageEventInterface extends \ArrayAccess, \IteratorAggregate
{
    /**
     * @return bool
     */
    public function isBulk();
}
