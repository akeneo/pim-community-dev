<?php

namespace Oro\Bundle\EmailBundle\Datagrid;

use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\UserBundle\Entity\User;

class EmailDatagridManager extends DatagridManager
{
    /**
     * @var User
     */
    protected $user;

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array();
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        $this->routeGenerator->setRouteParameters(array('id' => $user->getId()));
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $this->entityManager->getRepository('Oro\Bundle\EmailBundle\Entity\Email')->setUser($this->user);

        return parent::createQuery();
    }

    /**
     * @param ProxyQueryInterface $query
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        //$query->
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldName = new FieldDescription();
        $fieldName->setName('fromName');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('oro.user.datagrid.email.column.from_name'),
                'field_name'  => 'fromName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldName = new FieldDescription();
        $fieldName->setName('subject');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('oro.user.datagrid.email.column.subject'),
                'field_name'  => 'subject',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldName = new FieldDescription();
        $fieldName->setName('received');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('oro.user.datagrid.email.column.received'),
                'field_name'  => 'received',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldEntityName = new FieldDescription();
        $fieldEntityName->setName('recipients');
        $fieldEntityName->setOptions(
            array(
                'type'                => FieldDescriptionInterface::TYPE_HTML,
                'label'               => $this->translate('oro.email.datagrid.recipients'),
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
            $fieldEntityName,
            'OroEmailBundle:Email:Datagrid/Property/recipients.html.twig'
        );
        $fieldEntityName->setProperty($templateDataProperty);
        $fieldsCollection->add($fieldEntityName);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        return array();
    }
}
