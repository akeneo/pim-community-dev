<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class AuditFieldDatagridManager extends AuditDatagrid
{
    /**
     * @var string
     */
    public $fieldName;

    /**
     * @param ProxyQueryInterface $query
     * @return ProxyQueryInterface|void
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        parent::prepareQuery($query);

        $query->innerJoin('log.diffs', 'diff', 'WITH', 'diff.className = :className AND diff.fieldName = :fieldName');
        $query->setParameters(
            array(
                'className' => $this->entityClass,
                'fieldName' => $this->fieldName,
            )
        );

        return $query;
    }

    protected function getOptions()
    {
        return array(
            'is_entity'  => false,
            'field_name' => $this->fieldName
        );
    }

}
