<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

use Pim\Bundle\GridBundle\Filter\FilterInterface;

/**
 * Audit datagrid
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditDatagridManager extends DatagridManager
{
    /**
     * @var string
     */
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
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = $this->createFieldAction();
        $fieldsCollection->add($field);

        $field = $this->createFieldVersion();
        $fieldsCollection->add($field);

        $field = $this->createFieldData();
        $fieldsCollection->add($field);

        $field = $this->createFieldAuthor();
        $fieldsCollection->add($field);

        $field = $this->createFieldLoggedAt();
        $fieldsCollection->add($field);
    }

    /**
     * Create field action
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFieldAction()
    {
        $field = new FieldDescription();
        $field->setName('action');
        $field->setOptions(
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

        return $field;
    }

    /**
     * Create field version
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFieldVersion()
    {
        $field = new FieldDescription();
        $field->setName('version');
        $field->setOptions(
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

        return $field;
    }

    /**
     * Create field data
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFieldData()
    {
        $field = new FieldDescription();
        $field->setName('data');
        $field->setOptions(
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
        $field->setProperty(
            new TwigTemplateProperty(
                $field,
                'OroDataAuditBundle:Datagrid:Property/data.html.twig'
            )
        );

        return $field;
    }

    /**
     * Create field author
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFieldAuthor()
    {
        $field = new FieldDescription();
        $field->setName('author');
        $field->setOptions(
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
        $field->setFieldName('author');

        return $field;
    }

    /**
     * Create logged at field
     *
     * @return \Oro\Bundle\GridBundle\Field\FieldDescription
     */
    protected function createFieldLoggedAt()
    {
        $field = new FieldDescription();
        $field->setName('logged');
        $field->setOptions(
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

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareQuery(ProxyQueryInterface $proxyQuery)
    {
        $rootAlias = $proxyQuery->getRootAlias();

        $proxyQuery
            ->addSelect($rootAlias, true)
            ->addSelect('u', true)
            ->addSelect($this->authorExpression . ' AS author', true);

        $proxyQuery
            ->leftJoin(sprintf('%s.user', $rootAlias), 'u');
    }

    /**
     * {@inheritDoc}
     */
    public function getToolbarOptions()
    {
        $removeActions = array('addResetAction' => false);

        return array_merge($removeActions, $this->toolbarOptions);
    }
}
