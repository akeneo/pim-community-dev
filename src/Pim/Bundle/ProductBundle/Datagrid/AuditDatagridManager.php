<?php

namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Pim\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

/**
 * Audit datagrid
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditDatagridManager extends DatagridManager
{
    protected $authorExpression = <<<DQL
CONCAT(
    CONCAT(
        CONCAT(u.firstName, ' '),
        CONCAT(u.lastName, ' ')
    ),
    CONCAT(' - ', u.email)
)
DQL;

    /**
     * {@inheritdoc}
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
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
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
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldVersion);

        $fieldData = new FieldDescription();
        $fieldData->setName('data');
        $fieldData->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Data',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
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
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Author',
                'field_name'  => 'author',
                'expression'  => $this->authorExpression,
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
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
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldLogged);
    }

    /**
     * {@inheritdoc}
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
}
