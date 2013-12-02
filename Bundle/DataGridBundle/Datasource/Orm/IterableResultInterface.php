<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm;

use Doctrine\ORM\QueryBuilder;

interface IterableResultInterface extends \Iterator
{
    /**
     * Sets buffer size that can be used to optimize resources usage during iterations
     *
     * @param int $size
     */
    public function setBufferSize($size);

    /**
     * @return QueryBuilder
     */
    public function getSource();
}
