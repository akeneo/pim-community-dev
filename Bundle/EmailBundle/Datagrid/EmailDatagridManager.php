<?php

namespace Oro\Bundle\EmailBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class EmailDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldName = new FieldDescription();
        $fieldName->setName('fromEmailAddress');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => $this->translate('oro.email.datagrid.email.from_name'),
                'field_name'  => 'fromEmailAddress',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldName,
            'OroEmailBundle:Email:Datagrid/Property/from.html.twig'
        );
        $fieldName->setProperty($templateDataProperty);

        $fieldsCollection->add($fieldName);

        $fieldName = new FieldDescription();
        $fieldName->setName('subject');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => $this->translate('oro.email.datagrid.email.subject'),
                'field_name'  => 'subject',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldName,
            'OroEmailBundle:Email:Datagrid/Property/subject.html.twig'
        );
        $fieldName->setProperty($templateDataProperty);

        $fieldsCollection->add($fieldName);

        $fieldName = new FieldDescription();
        $fieldName->setName('sentAt');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('oro.email.datagrid.email.sentAt'),
                'field_name'  => 'sentAt',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldName = new FieldDescription();
        $fieldName->setName('recipients');
        $fieldName->setOptions(
            array(
                'type'                => FieldDescriptionInterface::TYPE_HTML,
                'label'               => $this->translate('oro.email.datagrid.email.recipients'),
                'field_name'          => 'recipients',
                'filter_type'         => FilterInterface::TYPE_CHOICE,
                'choices'             => array(),
                'translation_domain'  => 'config',
                'required'            => false,
                'sortable'            => false,
                'filterable'          => true,
                'show_filter'         => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldName,
            'OroEmailBundle:Email:Datagrid/Property/recipients.html.twig'
        );
        $fieldName->setProperty($templateDataProperty);
        $fieldsCollection->add($fieldName);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultSorters()
    {
        return array('sentAt' => SorterInterface::DIRECTION_DESC);
    }
}
