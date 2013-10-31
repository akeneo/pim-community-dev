<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

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

    /**
     * @return ProxyQuery|Query|QueryBuilder
     */
    public function getSource();
}
