<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

/**
 * Product attribute grid manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $baseActionUrl;

    /**
     * set router
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * get properties
     * @return array
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('edit_link', $this->router, 'pim_product_productattribute_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_product_productattribute_remove', array('id')),
        );
    }

    /**
     * @return FieldDescriptionCollection
     */
    protected function getFieldDescriptionCollection()
    {
        if (!$this->fieldsCollection) {
            $this->fieldsCollection = new FieldDescriptionCollection();

            $fieldId = new FieldDescription();
            $fieldId->setName('id');
            $fieldId->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                    'label'       => 'ID',
                    'field_name'  => 'id',
                    'filter_type' => FilterInterface::TYPE_NUMBER,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($fieldId);

            $field = new FieldDescription();
            $field->setName('code');
            $field->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_TEXT,
                    'label'       => 'Code',
                    'field_name'  => 'code',
                    'filter_type' => FilterInterface::TYPE_STRING,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($field);

            $field = new FieldDescription();
            $field->setName('attributeType');
            $field->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_TEXT,
                    'label'       => 'Type',
                    'field_name'  => 'attributeType',
                    'filter_type' => FilterInterface::TYPE_STRING,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($field);

            $field = new FieldDescription();
            $field->setName('scopable');
            $field->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                    'label'       => 'Scopable',
                    'field_name'  => 'scopable',
                    'filter_type' => FilterInterface::TYPE_NUMBER,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($field);

            $field = new FieldDescription();
            $field->setName('translatable');
            $field->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                    'label'       => 'Translatable',
                    'field_name'  => 'translatable',
                    'filter_type' => FilterInterface::TYPE_NUMBER,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($field);
        }

        return $this->fieldsCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescription */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isFilterable()) {
                $fields[] = $fieldDescription;
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSorters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescription */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isSortable()) {
                $fields[] = $fieldDescription;
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => 'Edit',
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true,
                'runOnRowClick' => true
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label'=> 'Delete',
                'icon' => 'trash',
                'link' => 'delete_link',
            )
        );

        return array($editAction, $deleteAction);
    }
}
