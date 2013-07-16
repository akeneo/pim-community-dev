<?php

namespace Oro\Bundle\NotificationBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\CallbackProperty;
use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Property\TranslateableProperty;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class EmailNotificationDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('update_link', $this->router, 'oro_notification_emailnotification_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_emailnotication', array('id')),
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
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('ID'),
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'show_column' => false
            )
        );
        $fieldsCollection->add($fieldId);

        $fieldEntityName = new FieldDescription();
        $fieldEntityName->setName('entityName');
        $fieldEntityName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('oro.notification.datagrid.entity_name'),
                'field_name'  => 'entityName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldEntityName,
            'OroNotificationBundle:EmailNotification:Datagrid/Property/entityName.html.twig'
        );
        $fieldEntityName->setProperty($templateDataProperty);
        $fieldsCollection->add($fieldEntityName);

        $fieldTemplate = new FieldDescription();
        $fieldTemplate->setName('template');
        $fieldTemplate->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('oro.notification.datagrid.template'),
                'field_name'  => 'template',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldTemplate);

        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('recipientList');
        $fieldRecipientList->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('oro.notification.datagrid.recipients'),
                'field_name'  => 'recipientList',
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldRecipientList);

        $fieldEvent = new FieldDescription();
        $fieldEvent->setName('eventName');
        $fieldEvent->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => $this->translate('oro.notification.datagrid.event_name'),
                'field_name'  => 'eventName',
                'expression'  => 'eventName',
                'filter_type' => FilterInterface::TYPE_ENTITY,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_filter' => true,
                // entity filter options
                'multiple'          => true,
                'class'             => 'OroNotificationBundle:Event',
                'property'          => 'name',
                'filter_by_where'   => true,
                'query_builder'     => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e');
                },
            )
        );

        $property = new TranslateableProperty('eventName', $this->translator);
        $fieldEvent->setProperty($property);
        $fieldsCollection->add($fieldEvent);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_notification_emailnotification_update',
            'options'      => array(
                'label'         => $this->translate('oro.notification.datagrid.action.update'),
                'link'          => 'update_link',
                'runOnRowClick' => true,
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'oro_notification_emailnotification_update',
            'options'      => array(
                'label'   => $this->translate('oro.notification.datagrid.action.update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'oro_notification_emailnotification_remove',
            'options'      => array(
                'label' => $this->translate('oro.notification.datagrid.action.delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $updateAction, $deleteAction);
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();

        /** @var $query QueryBuilder */
        $query->addSelect('event.name as eventName', true);
        $query->leftJoin($entityAlias . '.event', 'event');
    }
}
