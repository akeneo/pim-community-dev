<?php

namespace Oro\Bundle\DataAuditBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class AuditDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var string
     */
    protected $authorExpression =
        'CONCAT(
            CONCAT(
                CONCAT(u.firstName, \' \'),
                CONCAT(u.lastName, \' \')
            ),
            CONCAT(\' - \', u.email)
        )';

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldAction = new FieldDescription();
        $fieldAction->setName('action');
        $fieldAction->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Action',
                'field_name'  => 'action',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices'     => array(
                    LoggableManager::ACTION_UPDATE => 'Updated',
                    LoggableManager::ACTION_CREATE => 'Created',
                    LoggableManager::ACTION_REMOVE => 'Deleted',
                ),
                'multiple' => true,
            )
        );
        $fieldsCollection->add($fieldAction);

        $fieldVersion = new FieldDescription();
        $fieldVersion->setName('version');
        $fieldVersion->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Version',
                'field_name'  => 'version',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldVersion);

        $fieldObjectClass = new FieldDescription();
        $fieldObjectClass->setName('objectClass');
        $fieldObjectClass->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_OPTIONS,
                'label'       => 'Entity Type',
                'field_name'  => 'objectClass',
                'filter_type' => FilterInterface::TYPE_CHOICE,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'choices'     => $this->getObjectClassOptions(),
                'multiple'    => true,
            )
        );
        $fieldsCollection->add($fieldObjectClass);

        $fieldObjectName = new FieldDescription();
        $fieldObjectName->setName('objectName');
        $fieldObjectName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Entity Name',
                'field_name'  => 'objectName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldObjectName);

        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('objectId');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Entity Id',
                'field_name'  => 'objectId',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldObjectId);

        $fieldData = new FieldDescription();
        $fieldData->setName('data');
        $fieldData->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Data',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $templateDataProperty = new TwigTemplateProperty(
            $fieldData,
            'OroDataAuditBundle:Datagrid:Property/data.html.twig'
        );
        $fieldData->setProperty($templateDataProperty);
        $fieldsCollection->add($fieldData);

        $fieldAuthor = new FieldDescription();
        $fieldAuthor->setName('author');
        $fieldAuthor->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => 'Author',
                'field_name'      => 'author',
                'expression'      => $this->authorExpression,
                'filter_type'     => FilterInterface::TYPE_STRING,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true
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
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldLogged);
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

        $query->leftJoin('a.user', 'u');
        $query->addSelect('a', true);
        $query->addSelect('u', true);
        $query->addSelect($this->authorExpression . ' AS author', true);

        return $query;
    }

    /**
     * TODO Refactor this method to get rid of createQuery usage
     *
     * Get distinct object classes
     *
     * @return array
     */
    protected function getObjectClassOptions()
    {
        $options = array();

        $query = $this->createQuery()->getQueryBuilder()
            ->add('select', 'a.objectClass')
            ->add('from', 'Oro\Bundle\DataAuditBundle\Entity\Audit a')
            ->distinct('a.objectClass');

        $result = $query->getQuery()->getArrayResult();

        foreach ((array) $result as $value) {
            $options[$value['objectClass']] = current(array_reverse(explode('\\', $value['objectClass'])));
        }

        return $options;
    }
}
