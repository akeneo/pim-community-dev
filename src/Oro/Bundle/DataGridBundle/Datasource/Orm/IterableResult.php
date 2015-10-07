<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm;

use Doctrine\ORM\Query;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\ORM\Query\BufferedQueryResultIterator;

/**
 * Iterates query result with elements of ResultRecord type
 */
class IterableResult extends BufferedQueryResultIterator implements IterableResultInterface
{
    /**
     * {@inheritDoc}
     */
    public function next()
    {
        parent::next();

        if (null !== $this->current) {
            $result        = new ResultRecord($this->current);
            $this->current = $result;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->source;
    }
}
