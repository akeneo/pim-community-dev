<?php

namespace Oro\Bundle\EntityConfigBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class ConfigDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

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


//        $fieldVersion = new FieldDescription();
//        $fieldVersion->setName('version');
//        $fieldVersion->setOptions(
//            array(
//                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
//                'label'       => 'Version',
//                'field_name'  => 'version',
//                'filter_type' => FilterInterface::TYPE_NUMBER,
//                'required'    => false,
//                'sortable'    => true,
//                'filterable'  => true,
//                'show_filter' => false,
//            )
//        );
//        $fieldsCollection->add($fieldVersion);

//        $fieldObjectClass = new FieldDescription();
//        $fieldObjectClass->setName('objectClass');
//        $fieldObjectClass->setOptions(
//            array(
//                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
//                'label'       => 'Entity Type',
//                'field_name'  => 'objectClass',
//                'filter_type' => FilterInterface::TYPE_CHOICE,
//                'required'    => false,
//                'sortable'    => true,
//                'filterable'  => true,
//                'show_filter' => true,
//                'choices'     => $this->getObjectClassOptions(),
//                'multiple'    => true,
//            )
//        );
//        $fieldsCollection->add($fieldObjectClass);

        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('Id');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Id',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldObjectId);

        $fieldObjectName = new FieldDescription();
        $fieldObjectName->setName('className');
        $fieldObjectName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Class Name',
                'field_name'  => 'className',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldObjectName);



//        $fieldData = new FieldDescription();
//        $fieldData->setName('data');
//        $fieldData->setOptions(
//            array(
//                'type'        => FieldDescriptionInterface::TYPE_HTML,
//                'label'       => 'Data',
//                'field_name'  => 'data',
//                'filter_type' => FilterInterface::TYPE_STRING,
//                'required'    => false,
//                'sortable'    => true,
//                'filterable'  => true,
//                'show_filter' => true,
//            )
//        );
//        $templateDataProperty = new TwigTemplateProperty(
//            $fieldData,
//            'OroDataAuditBundle:Datagrid:Property/data.html.twig'
//        );
//        $fieldData->setProperty($templateDataProperty);
//        $fieldsCollection->add($fieldData);
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

}
