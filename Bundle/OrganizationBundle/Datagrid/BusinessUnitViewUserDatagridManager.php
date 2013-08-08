<?php

namespace Oro\Bundle\OrganizationBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

use Oro\Bundle\OrganizationBundle\Datagrid\BusinessUnitUpdateUserDatagridManager;

class BusinessUnitViewUserDatagridManager extends BusinessUnitUpdateUserDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function createUserRelationColumn()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();
        $query->andWhere(":business_unit MEMBER OF $entityAlias.businessUnits");
        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        return array('business_unit' => $this->getBusinessUnit());
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'lastName'  => SorterInterface::DIRECTION_ASC,
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        return array();
    }
}
