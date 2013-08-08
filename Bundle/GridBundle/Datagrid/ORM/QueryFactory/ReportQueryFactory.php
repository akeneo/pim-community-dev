<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\ReportProxyQuery;

class ReportQueryFactory extends QueryFactory
{
    /**
     * @return ProxyQueryInterface
     * @throws \LogicException
     */
    public function createQuery()
    {
        if (!$this->queryBuilder) {
            throw new \LogicException('Can\'t create datagrid query. Query builder is not configured.');
        }

        return new ReportProxyQuery($this->queryBuilder);
    }
}
