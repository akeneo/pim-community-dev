<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;

/**
 * Iterates query result with elements of ResultRecord type
 */
class IterableResult extends BufferedQueryResultIterator implements IterableResultInterface
{
    /**
     * @return IterableResult
     */
    public static function createFromProxyQuery(ProxyQuery $proxyQuery)
    {
        return static::createFromQueryBuilder($proxyQuery->getQueryBuilder());
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $result = parent::next();

        if (null !== $result) {
            $result = new ResultRecord($result);
            $this->current = $result;
        }

        return $result;
    }
}
