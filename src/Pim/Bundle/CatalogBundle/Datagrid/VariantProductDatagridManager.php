<?php

namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Product datagrid to link products to variant groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductDatagridManager extends FlexibleDatagridManager
{
    /**
     * @var VariantGroup $variantGroup
     */
    protected $variantGroup;

    /**
     * @var ProductManager $productManager
     */
    protected $productManager;

    /**
     * {@inheritdoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $includedTypes = array(
            'pim_catalog_identifier',
            'pim_catalog_simpleselect',
            'pim_catalog_multiselect'
        );

        foreach ($this->getFlexibleAttributes() as $attribute) {
            $attributeType = $attribute->getAttributeType();
            if (!in_array($attributeType, $includedTypes)) {
                continue;
            }

            // TODO : Hide filters where attribute is not in the variant

            $field = $this->createFlexibleField($attribute);
            $fieldsCollection->add($field);
        }

        $field = $this->createFamilyField();
        $fieldsCollection->add($field);
    }

    protected function createFamilyField()
    {
        $field = new FieldDescription();
        $field->setName('family');
        $field->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Family'),
                'field_name'      => 'familyLabel',
                'expression'      => 'family',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'required'        => false,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                'multiple'        => true,
                'class'           => 'PimCatalogBundle:Family',
                'property'        => 'label',
                'filter_by_where' => true
            )
        );

        return $field;
    }

    /**
     * Set a variant group
     *
     * @param VariantGroup $variantGroup
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\VariantProductDatagridManager
     */
    public function setVariantGroup(VariantGroup $variantGroup)
    {
        $this->variantGroup = $variantGroup;

        return $this;
    }

    /**
     * Set a product manager
     *
     * @param ProductManager $productManager
     *
     * @return \Pim\Bundle\CatalogBundle\Datagrid\VariantProductDatagridManager
     */
    public function setProductManager(ProductManager $productManager)
    {
        $this->productManager = $productManager;

        return $this;
    }
}
