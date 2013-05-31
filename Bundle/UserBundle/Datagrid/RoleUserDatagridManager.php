<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class RoleUserDatagridManager extends UserRelationDatagridManager
{
    /**
     * @var Role
     */
    private $role;

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
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => 'Has role',
                'field_name'  => 'hasCurrentRole',
                'expression'  => 'hasCurrentRole',
                'nullable'    => false,
                'editable'    => true,
                'sortable'    => true,
                'filter_type' => FilterInterface::TYPE_BOOLEAN,
                'filterable'  => true,
                'show_filter' => true,
            )
        );

        return $fieldHasRole;
    }

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        $query = parent::createQuery();
        if ($this->getRole()->getId()) {
            $query->addSelect(
                'CASE WHEN ' .
                '(:role MEMBER OF u.roles OR u.id IN (:data_in)) AND u.id NOT IN (:data_not_in) '.
                'THEN 1 ELSE 0 END AS hasCurrentRole',
                true
            );
        } else {
            $query->addSelect(
                ' 0 as hasCurrentRole',
                true
            );
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        return array_merge(
            parent::getQueryParameters(),
            array('role' => $this->getRole())
        );
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
