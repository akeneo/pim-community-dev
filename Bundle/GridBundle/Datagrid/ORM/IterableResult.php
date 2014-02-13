<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\Query;

use Oro\Bundle\DataGridBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;

/**
 * Iterates query result with elements of ResultRecord type
 */
class IterableResult extends BufferedQueryResultIterator implements IterableResultInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getQueryBy($source)
    {
        if ($source instanceof ProxyQuery) {
            return parent::getQueryBy($source->getQueryBuilder());
        } else {
            return parent::getQueryBy($source);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        parent::next();

        if (null !== $this->current) {
            $result = new ResultRecord($this->current);
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
