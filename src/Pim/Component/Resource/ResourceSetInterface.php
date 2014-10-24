<?php

namespace Pim\Component\Resource;

/**
 * Resource set interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceSetInterface extends \ArrayAccess, \Iterator
{
    /**
     * Get the type of resource in this set.
     *
     * @return string|null
     */
    public function getResourceClass();

    /**
     * Get the resources of the set.
     *
     * @return ResourceInterface[]
     */
    public function getResources();
}
