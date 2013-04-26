<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Grid manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductDatagridManager extends FlexibleDatagridManager
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
     * @var array
     */
    protected static $typeMatches = array(
        AbstractAttributeType::BACKEND_TYPE_DATE => array(
            'field'  => FieldDescriptionInterface::TYPE_DATE,
            'filter' => FilterInterface::TYPE_FLEXIBLE_DATE,
        ),
        AbstractAttributeType::BACKEND_TYPE_DATETIME => array(
            'field'  => FieldDescriptionInterface::TYPE_DATETIME,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_DECIMAL => array(
            'field'  => FieldDescriptionInterface::TYPE_DECIMAL,
            'filter' => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        ),
        AbstractAttributeType::BACKEND_TYPE_INTEGER => array(
            'field'  => FieldDescriptionInterface::TYPE_INTEGER,
            'filter' => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTION => array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTIONS => array(
            'field'  => FieldDescriptionInterface::TYPE_OPTIONS,
            'filter' => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        ),
        AbstractAttributeType::BACKEND_TYPE_TEXT => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_VARCHAR => array(
            'field' => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_PRICE => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
        AbstractAttributeType::BACKEND_TYPE_METRIC => array(
            'field'  => FieldDescriptionInterface::TYPE_TEXT,
            'filter' => FilterInterface::TYPE_FLEXIBLE_STRING,
        ),
    );

    /**
     * set router
     *
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
            new UrlProperty('edit_link', $this->router, 'pim_product_product_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_product_product_remove', array('id')),
        );
    }

    /**
     * get field description
     * @return FieldDescriptionCollection
     */
    protected function getFieldDescriptionCollection()
    {
        if (!$this->fieldsCollection) {
            $this->fieldsCollection = new FieldDescriptionCollection();

            $fieldSku = new FieldDescription();
            $fieldSku->setName('sku');
            $fieldSku->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_TEXT,
                    'label'       => $this->translator->trans('Sku'),
                    'field_name'  => 'sku',
                    'filter_type' => FilterInterface::TYPE_STRING,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($fieldSku);

            // TODO : until we'll have related backend type in grid bundle
            $excludedBackend = array(
                AbstractAttributeType::BACKEND_TYPE_MEDIA,
                AbstractAttributeType::BACKEND_TYPE_METRIC,
                AbstractAttributeType::BACKEND_TYPE_PRICE
            );

            foreach ($this->getFlexibleAttributes() as $attribute) {

                $backendType   = $attribute->getBackendType();
                if (in_array($backendType, $excludedBackend)) {
                    continue;
                }

                if (!$attribute->getUseableAsGridColumn()) {
                    continue;
                }

                $attributeType = $this->convertFlexibleTypeToFieldType($backendType);
                $filterType    = $this->convertFlexibleTypeToFilterType($backendType);

                $field = new FieldDescription();
                $field->setName($attribute->getCode());
                $field->setOptions(
                    array(
                        'type'          => $attributeType,
                        'label'         => $attribute->getName(),
                        'field_name'    => $attribute->getCode(),
                        'filter_type'   => $filterType,
                        'required'      => false,
                        'sortable'      => true,
                        'filterable'    => true,
                        'flexible_name' => $this->flexibleManager->getFlexibleName()
                    )
                );

                if ($attributeType == FieldDescriptionInterface::TYPE_OPTIONS) {
                    $field->setOption('multiple', true);
                }

                if (!$attribute->getUseableAsGridFilter()) {
                    $field->setOption('filter_type', false);
                    $field->setOption('filterable', false);
                }

                $this->fieldsCollection->add($field);
            }
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
    protected function getRowActions()
    {
        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translator->trans('Edit'),
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
                'label'=> $this->translator->trans('Delete'),
                'icon' => 'trash',
                'link' => 'delete_link',
            )
        );

        return array($editAction, $deleteAction);
    }

    /**
     * Override to add support for price and metric
     *
     * @param string $flexibleFieldType
     *
     * @return string
     * @throws \LogicException
     */
    public function convertFlexibleTypeToFieldType($flexibleFieldType)
    {
        if (!isset(self::$typeMatches[$flexibleFieldType]['field'])) {
            throw new \LogicException('Unknown flexible backend field type.');
        }

        return self::$typeMatches[$flexibleFieldType]['field'];
    }

    /**
     * Override to add support for price and metric
     *
     * @param string $flexibleFieldType
     *
     * @return string
     * @throws \LogicException
     */
    public function convertFlexibleTypeToFilterType($flexibleFieldType)
    {
        if (!isset(self::$typeMatches[$flexibleFieldType]['filter'])) {
            throw new \LogicException('Unknown flexible backend filter type.');
        }

        return self::$typeMatches[$flexibleFieldType]['filter'];
    }
}
