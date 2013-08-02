<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class AuditDatagridManager extends AuditDatagrid
{
    /**
     * @param ProxyQueryInterface $query
     * @return ProxyQueryInterface|void
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        parent::prepareQuery($query);

        $query->innerJoin('log.diffs', 'diff', 'WITH', 'diff.className = :className AND diff.fieldName IS NULL');
        $query->setParameter('className', $this->entityClass);

        return $query;
    }

    protected function getOptions()
    {
        return array(
            'is_entity' => true
        );
    }
}
