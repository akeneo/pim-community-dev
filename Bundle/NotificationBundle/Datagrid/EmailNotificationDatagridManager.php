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
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('recipientGroupsList');
        $fieldRecipientList->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'field_name'  => 'recipientGroupsList',
                'expression'  => 'recipientGroupsList',
                'label'       => $this->translate('oro.notification.datagrid.recipient.group'),
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_filter' => true,
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




        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('emailRecipient');
        $fieldRecipientList->setOptions(
            array(
                'type'               => FieldDescriptionInterface::TYPE_TEXT,
                'field_name'         => 'emailRecipient',
                'expression'         => 'recipientList.email',
                'label'              => $this->translate('oro.notification.datagrid.recipient.custom_email'),
                'required'           => false,
                'sortable'           => false,
                'show_filter'        => true,
                'filterable'         => true,
                'filter_by_having'   => true,
                'filter_type'        => FilterInterface::TYPE_STRING
            )
        );
        $fieldsCollection->add($fieldRecipientList);





        $fieldRecipientList = new FieldDescription();
        $fieldRecipientList->setName('ownerRecipient');
        $fieldRecipientList->setOptions(
            array(
                'type'               => FieldDescriptionInterface::TYPE_BOOLEAN,
                'field_name'         => 'ownerRecipient',
                'expression'         => 'recipientList.owner',
                'label'              => $this->translate('oro.notification.datagrid.recipient.owner'),
                'required'           => false,
                'sortable'           => false,
                'filterable'         => true,
                'show_filter'        => true,
                'filter_by_having'   => true,
                'filter_type'        => FilterInterface::TYPE_BOOLEAN
            )
        );
        $fieldsCollection->add($fieldRecipientList);
    }

}
