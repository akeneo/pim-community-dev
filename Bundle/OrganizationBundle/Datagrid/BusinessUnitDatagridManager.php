<?php

namespace Oro\Bundle\OrganizationBundle\Datagrid;

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

class BusinessUnitDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'oro_business_unit_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_business_unit_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_businessunit', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Email',
                'field_name'  => 'email',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldEmail);

        $fieldPhone = new FieldDescription();
        $fieldPhone->setName('phone');
        $fieldPhone->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Phone',
                'field_name'  => 'phone',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldPhone);

        $parent = new FieldDescription();
        $parent->setName('parent');
        $parent->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Parent',
                'field_name'  => 'parentName',
                'expression'  => 'parent',
                'filter_type' => FilterInterface::TYPE_ENTITY,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                // entity filter options
                'class'           => 'OroOrganizationBundle:BusinessUnit',
                'property'        => 'name',
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($parent);

        $organization = new FieldDescription();
        $organization->setName('organization');
        $organization->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Organization',
                'field_name'  => 'organizationName',
                'expression'  => 'organization',
                'filter_type' => FilterInterface::TYPE_ENTITY,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                // entity filter options
                'class'           => 'OroOrganizationBundle:Organization',
                'property'        => 'name',
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($organization);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created_at');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Created at',
                'field_name'  => 'created_at',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldCreated);
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();
        $query->addSelect('organization.name as organizationName', true);
        $query->addSelect('parent.name as parentName', true);
        /** @var $query QueryBuilder */
        $query->leftJoin($entityAlias . '.organization', 'organization');
        $query->leftJoin($entityAlias . '.parent', 'parent');
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $businessUnitClickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_business_unit_view',
            'options'      => array(
                'label'         => 'View',
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $businessUnitViewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_business_unit_view',
            'options'      => array(
                'label' => 'View',
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $businessUnitUpdateAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_business_unit_update',
            'options'      => array(
                'label' => 'Update',
                'icon'  => 'edit',
                'link'  => 'update_link',
            )
        );

        $businessUnitDeleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'oro_business_unit_delete',
            'options'      => array(
                'label' => 'Delete',
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array(
            $businessUnitClickAction,
            $businessUnitViewAction,
            $businessUnitUpdateAction,
            $businessUnitDeleteAction
        );
    }
}
