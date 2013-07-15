<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

class RoleUserDatagridManager extends UserRelationDatagridManager
{
    /**
     * @var Role
     */
    protected $role;

    /**
     * @var string
     */
    protected $hasRoleExpression;

    /**
     * @param Role $role
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @return Role
     * @throws \LogicException When group is not set
     */
    public function getRole()
    {
        if (!$this->role) {
            throw new \LogicException('Datagrid manager has no configured Role entity');
        }

        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    protected function createUserRelationColumn()
    {
        $fieldHasRole = new FieldDescription();
        $fieldHasRole->setName('has_role');
        $fieldHasRole->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => 'Has role',
                'field_name'      => 'hasCurrentRole',
                'expression'      => $this->getHasRoleExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true
            )
        );

        return $fieldHasRole;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $query->addSelect($this->getHasRoleExpression() . ' AS hasCurrentRole', true);

        return $query;
    }

    /**
     * @return string
     */
    protected function getHasRoleExpression()
    {
        if (null === $this->hasRoleExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

            if ($this->getRole()->getId()) {
                $this->hasRoleExpression =
                    "CASE WHEN " .
                    "(:role MEMBER OF $entityAlias.roles OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasRoleExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) " .
                    "THEN true ELSE false END";
            }
        }

        return $this->hasRoleExpression;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $parameters = parent::getQueryParameters();

        if ($this->getRole()->getId()) {
            $parameters['role'] = $this->getRole();
        }

        return $parameters;
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_role' => SorterInterface::DIRECTION_DESC,
            'lastName' => SorterInterface::DIRECTION_ASC,
        );
    }
}
