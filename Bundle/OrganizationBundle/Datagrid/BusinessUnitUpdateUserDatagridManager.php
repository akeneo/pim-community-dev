<?php

namespace Oro\Bundle\OrganizationBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

use Oro\Bundle\UserBundle\Datagrid\UserRelationDatagridManager;

class BusinessUnitUpdateUserDatagridManager extends UserRelationDatagridManager
{
    /**
     * @var BusinessUnit
     */
    protected $businessUnit;

    /**
     * @var string
     */
    protected $hasBusinessUnitExpression;

    /**
     * @param BusinessUnit $businessUnit
     */
    public function setBusinessUnit(BusinessUnit $businessUnit)
    {
        $this->businessUnit = $businessUnit;
    }

    /**
     * @return BusinessUnit
     * @throws \LogicException When business unit is not set
     */
    public function getBusinessUnit()
    {
        if (!$this->businessUnit) {
            throw new \LogicException('Datagrid manager has no configured BusinessUnit entity');
        }

        return $this->businessUnit;
    }

    /**
     * {@inheritDoc}
     */
    protected function createUserRelationColumn()
    {
        $fieldHasBusinessUnit = new FieldDescription();
        $fieldHasBusinessUnit->setName('has_business_unit');
        $fieldHasBusinessUnit->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => 'Has business unit',
                'field_name'      => 'hasCurrentBusinessUnit',
                'expression'      => $this->getHasBusinessUnitExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true
            )
        );

        return $fieldHasBusinessUnit;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->addSelect($this->getHasBusinessUnitExpression() . ' AS hasCurrentBusinessUnit', true);

        return $query;
    }

    /**
     * @return string
     */
    protected function getHasBusinessUnitExpression()
    {
        if (null === $this->hasBusinessUnitExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

            if ($this->getBusinessUnit()->getId()) {
                $this->hasBusinessUnitExpression =
                    "CASE WHEN " .
                    "(:business_unit MEMBER OF $entityAlias.businessUnits OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasBusinessUnitExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) " .
                    "THEN true ELSE false END";
            }
        }

        return $this->hasBusinessUnitExpression;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $parameters = parent::getQueryParameters();

        if ($this->getBusinessUnit()->getId()) {
            $parameters['business_unit'] = $this->getBusinessUnit();
        }

        return $parameters;
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_business_unit' => SorterInterface::DIRECTION_DESC,
            'lastName'  => SorterInterface::DIRECTION_ASC,
        );
    }
}
