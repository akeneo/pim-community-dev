<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\Query;

/**
 * Uses fixed pager parameters
 */
class ConstantPagerIterableResult extends IterableResult
{
    /**
     * @param Query $pageQuery
     */
    protected function setPagerParameters(Query $pageQuery)
    {
        $pageQuery->setFirstResult($this->getFirstResult());
        $pageQuery->setMaxResults($this->bufferSize);
    }
}
