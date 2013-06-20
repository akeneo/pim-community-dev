<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\FixedProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\UserBundle\Entity\User;

class GroupDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('update_link', $this->router, 'oro_user_group_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_group', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type' => FieldDescriptionInterface::TYPE_INTEGER,
                'label' => 'ID',
                'field_name' => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required' => false,
                'sortable' => false,
                'filterable' => false,
                'show_filter' => false,
                'show_column' => false,
            )
        );
        $fieldsCollection->add($fieldId);

        $fieldName = new FieldDescription();
        $fieldName->setName('name');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Name',
                'field_name'  => 'name',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $rolesLabel = new FieldDescription();
        $rolesLabel->setName('roles');
        $rolesLabel->setProperty(new FixedProperty('roles', 'roleLabelsAsString'));
        $rolesLabel->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Roles',
                'field_name'  => 'roles',
                'expression'  => 'role',
                'filter_type' => FilterInterface::TYPE_ENTITY,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_filter' => true,
                // entity filter options
                'class'           => 'OroUserBundle:Role',
                'property'        => 'label',
                'filter_by_where' => true,
                'query_builder'   => function (EntityRepository $er) {
                    return $er->createQueryBuilder('role')
                        ->where('role.role != :anonymousRole')
                        ->setParameter('anonymousRole', User::ROLE_ANONYMOUS);
                },
            )
        );
        $fieldsCollection->add($rolesLabel);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $groupClickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => 'Edit',
                'link'          => 'update_link',
                'runOnRowClick' => true,
            )
        );

        $groupUpdateAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Edit',
                'icon'  => 'edit',
                'link'  => 'update_link',
            )
        );

        $groupDeleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Delete',
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($groupClickAction, $groupUpdateAction, $groupDeleteAction);
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();

        /** @var $query QueryBuilder */
        $query->leftJoin($entityAlias . '.roles', 'role');
    }
}
