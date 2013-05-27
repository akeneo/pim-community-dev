<?php

namespace Oro\Bundle\DataAuditBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class AuditHistoryDatagridManager extends AuditDatagridManager
{
    /**
     * @var entityClass
     */
    public $entityClass;

    /**
     * @var entityClassId
     */
    public $entityClassId;

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldAuthor = new FieldDescription();
        $fieldAuthor->setName('author');
        $fieldAuthor->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Author',
                'field_name'  => 'author',
                'expression'  => $this->authorExpression,
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldAuthor->setFieldName('author');
        $fieldsCollection->add($fieldAuthor);

        $fieldLogged = new FieldDescription();
        $fieldLogged->setName('logged');
        $fieldLogged->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Logged At',
                'field_name'  => 'loggedAt',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldLogged);

        $fieldDataOld = new FieldDescription();
        $fieldDataOld->setName('old');
        $fieldDataOld->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Old values',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateDataOldProperty = new TwigTemplateProperty(
            $fieldDataOld,
            'OroDataAuditBundle:Datagrid:Property/old.html.twig'
        );
        $fieldDataOld->setProperty($templateDataOldProperty);
        $fieldsCollection->add($fieldDataOld);

        $fieldDataNew = new FieldDescription();
        $fieldDataNew->setName('new');
        $fieldDataNew->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'New values',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateDataNewProperty = new TwigTemplateProperty(
            $fieldDataNew,
            'OroDataAuditBundle:Datagrid:Property/new.html.twig'
        );
        $fieldDataNew->setProperty($templateDataNewProperty);
        $fieldsCollection->add($fieldDataNew);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'logged' => SorterInterface::DIRECTION_DESC
        );
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

        $query->where('a.objectClass = :objectClass AND a.objectId = :objectId');
        $query->setParameters(
            array(
                'objectClass' => $this->entityClass,
                'objectId'    => $this->entityClassId
            )
        );

        return $query;
    }
}
