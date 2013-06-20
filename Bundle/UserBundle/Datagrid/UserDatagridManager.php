<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class UserDatagridManager extends FlexibleDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'oro_user_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_user_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_user', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $this->addFieldId($fieldsCollection);
        $this->addFieldUsername($fieldsCollection);
        $this->addFieldEmail($fieldsCollection);
        $this->addFieldFirstName($fieldsCollection);
        $this->addFieldLastName($fieldsCollection);
        $this->addFieldCreated($fieldsCollection);
        $this->addFieldUpdated($fieldsCollection);
        $this->addFieldStatus($fieldsCollection);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => 'View',
                'link'          => 'view_link',
                'route'         => 'oro_user_view',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'View',
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Edit',
                'icon'  => 'edit',
                'link'  => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Delete',
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $viewAction, $updateAction, $deleteAction);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldId(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                 'label'       => 'ID',
                 'field_name'  => 'id',
                 'filter_type' => FilterInterface::TYPE_NUMBER,
                 'required'    => false,
                 'sortable'    => false,
                 'filterable'  => false,
                 'show_filter' => false,
                 'show_column' => false,
            )
        );
        $fieldsCollection->add($fieldId);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldUsername(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldUsername = new FieldDescription();
        $fieldUsername->setName('username');
        $fieldUsername->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_TEXT,
                 'label'       => 'Username',
                 'field_name'  => 'username',
                 'filter_type' => FilterInterface::TYPE_STRING,
                 'required'    => false,
                 'sortable'    => true,
                 'filterable'  => true,
                 'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUsername);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldEmail(FieldDescriptionCollection $fieldsCollection)
    {
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
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldFirstName(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldFirstName = new FieldDescription();
        $fieldFirstName->setName('firstName');
        $fieldFirstName->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_TEXT,
                 'label'       => 'First name',
                 'field_name'  => 'firstName',
                 'filter_type' => FilterInterface::TYPE_STRING,
                 'required'    => false,
                 'sortable'    => true,
                 'filterable'  => true,
                 'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldFirstName);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldLastName(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldLastName = new FieldDescription();
        $fieldLastName->setName('lastName');
        $fieldLastName->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_TEXT,
                 'label'       => 'Last name',
                 'field_name'  => 'lastName',
                 'filter_type' => FilterInterface::TYPE_STRING,
                 'required'    => false,
                 'sortable'    => true,
                 'filterable'  => true,
                 'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldLastName);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldCreated(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created');
        $fieldCreated->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                 'label'       => 'Created at',
                 'field_name'  => 'created',
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
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldUpdated(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldUpdated = new FieldDescription();
        $fieldUpdated->setName('updated');
        $fieldUpdated->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                 'label'       => 'Updated at',
                 'field_name'  => 'updated',
                 'filter_type' => FilterInterface::TYPE_DATETIME,
                 'required'    => false,
                 'sortable'    => true,
                 'filterable'  => true,
                 'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUpdated);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function addFieldStatus(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldStatus = new FieldDescription();
        $fieldStatus->setName('enabled');
        $fieldStatus->setOptions(
            array(
                 'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                 'label'       => 'Status',
                 'field_name'  => 'enabled',
                 'filter_type' => FilterInterface::TYPE_CHOICE,
                 'required'    => false,
                 'sortable'    => true,
                 'filterable'  => true,
                 'show_filter' => true,
                 'choices'     => array(
                     0  => $this->translator->trans('Inactive', array(), 'OroUserBundle'),
                     1  => $this->translator->trans('Active', array(), 'OroUserBundle'),
                 ),
            )
        );
        $fieldsCollection->add($fieldStatus);
    }
}
