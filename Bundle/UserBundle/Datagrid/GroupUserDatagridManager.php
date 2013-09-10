<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

class GroupUserDatagridManager extends UserRelationDatagridManager
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @var string
     */
    protected $hasGroupExpression;

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     * @throws \LogicException When group is not set
     */
    public function getGroup()
    {
        if (!$this->group) {
            throw new \LogicException('Datagrid manager has no configured Group entity');
        }

        return $this->group;
    }

    /**
     * {@inheritDoc}
     */
    protected function createUserRelationColumn()
    {
        $fieldHasGroup = new FieldDescription();
        $fieldHasGroup->setName('has_group');
        $fieldHasGroup->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => 'Has group',
                'field_name'      => 'hasCurrentGroup',
                'expression'      => $this->getHasGroupExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true
            )
        );

        return $fieldHasGroup;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->addSelect($this->getHasGroupExpression() . ' AS hasCurrentGroup', true);

        return $query;
    }

    /**
     * @return string
     */
    protected function getHasGroupExpression()
    {
        if (null === $this->hasGroupExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

            if ($this->getGroup()->getId()) {
                $this->hasGroupExpression =
                    "CASE WHEN " .
                    "(:group MEMBER OF $entityAlias.groups OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasGroupExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) " .
                    "THEN true ELSE false END";
            }
        }

        return $this->hasGroupExpression;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $parameters = parent::getQueryParameters();

        if ($this->getGroup()->getId()) {
            $parameters['group'] = $this->getGroup();
        }

        return $parameters;
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_group' => SorterInterface::DIRECTION_DESC,
            'lastName'  => SorterInterface::DIRECTION_ASC,
        );
    }
}
