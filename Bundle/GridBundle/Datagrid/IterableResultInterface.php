<?php

namespace Oro\Bundle\GridBundle\Datagrid;

/**
 * Iterates ProxyQuery with elements of ResultRecord type
 */
interface IterableResultInterface extends \Iterator
{
    /**
     * Sets buffer size that can be used to optimize resources usage during iterations
     *
     * @param int $size
     */
    public function setBufferSize($size);
}
