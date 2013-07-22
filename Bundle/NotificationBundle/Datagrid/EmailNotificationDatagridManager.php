<?php

namespace Oro\Bundle\NotificationBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
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
     * @var array
     */
    protected $entityNameChoise = array();

    public function __construct($entitiesConfig = array())
    {
        $this->entityNameChoise = array_map(
            function ($value) {
                return isset($value['name'])? $value['name'] : '';
            },
            $entitiesConfig
        );
    }

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
                'type'                => FieldDescriptionInterface::TYPE_TEXT,
                'label'               => $this->translate('oro.notification.datagrid.entity_name'),
                'field_name'          => 'entityName',
                'filter_type'         => FilterInterface::TYPE_CHOICE,
                'choices'             => $this->entityNameChoise,
                'translation_domain'  => 'config',
                'required'            => false,
                'sortable'            => false,
                'filterable'          => true,
                'show_filter'         => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldEntityName,
            'OroNotificationBundle:EmailNotification:Datagrid/Property/entityName.html.twig'
        );
        $fieldEntityName->setProperty($templateDataProperty);
        $fieldsCollection->add($fieldEntityName);

        $fieldEvent = new FieldDescription();
        $fieldEvent->setName('event');
        $fieldEvent->setOptions(
            array(
                'type'                => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'               => $this->translate('oro.notification.datagrid.event_name'),
                'field_name'          => 'eventName',
                'expression'          => 'event',
                'filter_type'         => FilterInterface::TYPE_ENTITY,
                'required'            => false,
                'sortable'            => false,
                'filterable'          => true,
                'show_filter'         => true,
                // entity filter options
                'multiple'            => true,
                'class'               => 'OroNotificationBundle:Event',
                'property'            => 'name',
                'filter_by_where'     => true,
                'query_builder'       => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e');
                },
            )
        );

        $property = new TranslateableProperty('event', $this->translator, 'eventName');
        $fieldEvent->setProperty($property);
        $fieldsCollection->add($fieldEvent);

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
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => $this->translate('oro.notification.datagrid.recipients'),
                'field_name'  => 'recipientList',
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldRecipientList,
            'OroNotificationBundle:EmailNotification:Datagrid/Property/recipientList.html.twig'
        );
        $fieldRecipientList->setProperty($templateDataProperty);
        $fieldsCollection->add($fieldRecipientList);

        // Recipient filters
        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('userRecipient');
        $fieldRecipientList->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'field_name'  => 'userRecipient',
                'expression'  => 'userRecipient',
                'label'       => $this->translate('oro.notification.datagrid.recipient.user'),
                'show_column' => false,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_filter' => false,
                // entity filter options
                'multiple'            => true,
                'filter_type'         => FilterInterface::TYPE_ENTITY,
                'class'               => 'OroUserBundle:User',
                'property'            => 'fullName',
                'filter_by_where'     => true,
                'query_builder'       => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e');
                },
            )
        );

        $fieldsCollection->add($fieldRecipientList);

        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('groupRecipient');
        $fieldRecipientList->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'field_name'  => 'groupRecipient',
                'expression'  => 'groupRecipient',
                'label'       => $this->translate('oro.notification.datagrid.recipient.group'),
                'show_column' => false,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                // entity filter options
                'multiple'            => true,
                'filter_type'         => FilterInterface::TYPE_ENTITY,
                'class'               => 'OroUserBundle:Group',
                'property'            => 'name',
                'filter_by_where'     => true,
                'query_builder'       => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e');
                },
            )
        );
        $fieldsCollection->add($fieldRecipientList);

        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('emailRecipient');
        $fieldRecipientList->setOptions(
            array(
                'type'               => FieldDescriptionInterface::TYPE_TEXT,
                'field_name'         => 'recipientList.email',
                'expression'         => 'recipientList.email',
                'label'              => $this->translate('oro.notification.datagrid.recipient.custom_email'),
                'show_column'        => false,
                'required'           => false,
                'sortable'           => false,
                'filterable'         => true,
                'filter_by_where'    => true,
                'filter_type'        => FilterInterface::TYPE_STRING
            )
        );
        $fieldsCollection->add($fieldRecipientList);

        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('ownerRecipient');
        $fieldRecipientList->setOptions(
            array(
                'type'               => FieldDescriptionInterface::TYPE_TEXT,
                'field_name'         => 'recipientList.owner',
                'expression'         => 'recipientList.owner',
                'label'              => $this->translate('oro.notification.datagrid.recipient.owner'),
                'show_column'        => false,
                'required'           => false,
                'sortable'           => false,
                'filterable'         => true,
                'filter_by_where'    => true,
                'filter_type'        => FilterInterface::TYPE_BOOLEAN
            )
        );
        $fieldsCollection->add($fieldRecipientList);
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
        $query->leftJoin($entityAlias . '.recipientList', 'recipientList');
        $query->leftJoin('recipientList.users', 'userRecipient');
        $query->leftJoin('recipientList.groups', 'groupRecipient');
    }
}
