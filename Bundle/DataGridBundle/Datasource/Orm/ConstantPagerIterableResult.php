<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm;

use Doctrine\ORM\Query;

/**
 * Uses fixed pager parameters
 */
class ConstantPagerIterableResult extends IterableResult
{
    public function __construct($source)
    {
        parent::__construct($source);
        $source->setMaxResults($this->bufferSize);
    }

    /**
     * @param Query $pageQuery
     */
    protected function setPagerParameters(Query $pageQuery)
    {
        $pageQuery->setFirstResult($this->getFirstResult());
        $pageQuery->setMaxResults($this->bufferSize);
    }
}
