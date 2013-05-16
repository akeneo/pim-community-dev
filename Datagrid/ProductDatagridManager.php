<?php
namespace Pim\Bundle\ProductBundle\Datagrid;

use Oro\Bundle\GridBundle\Property\FieldProperty;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

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
     * get properties
     * @return array
     */
    protected function getProperties()
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'     => FieldDescriptionInterface::TYPE_INTEGER,
                'required' => true,
            )
        );

        return array(
            new FieldProperty($fieldId),
            new UrlProperty('edit_link', $this->router, 'pim_product_product_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'pim_product_product_remove', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
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
        $fieldsCollection->add($fieldSku);

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
                    'label'         => $attribute->getLabel(),
                    'field_name'    => $attribute->getCode(),
                    'filter_type'   => $filterType,
                    'required'      => false,
                    'sortable'      => true,
                    'filterable'    => $attribute->getUseableAsGridFilter(),
                    'flexible_name' => $this->flexibleManager->getFlexibleName(),
                    'show_filter'   => $attribute->getUseableAsGridFilter()
                )
            );

            if ($attributeType == FieldDescriptionInterface::TYPE_OPTIONS) {
                $field->setOption('multiple', true);
            }

            if (!$attribute->getUseableAsGridFilter()) {
                $field->setOption('filter_type', false);
                $field->setOption('filterable', false);
            }

            $fieldsCollection->add($field);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => $this->translator->trans('Edit'),
                'icon'          => 'edit',
                'link'          => 'edit_link',
                'backUrl'       => true,
                'runOnRowClick' => true
            )
        );

        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translator->trans('Edit'),
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => $this->translator->trans('Delete'),
                'icon'    => 'trash',
                'link'    => 'delete_link'
            )
        );

        return array($clickAction, $editAction, $deleteAction);
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
