<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Attribute type manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeManager
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param ProductManager       $productManager Product manager
     * @param LocaleManager        $localeManager  Locale manager
     * @param AttributeTypeFactory $factory        Attribute type factory
     */
    public function __construct(
        ProductManager $productManager,
        LocaleManager $localeManager,
        AttributeTypeFactory $factory
    ) {
        $this->productManager = $productManager;
        $this->localeManager = $localeManager;
        $this->factory = $factory;
    }

    /**
     * Create a ProductAttribute object from data in the form
     *
     * @param array $data Form data
     *
     * @return ProductAttribute $attribute | null
     */
    public function createAttributeFromFormData($data)
    {
        if ($data instanceof ProductAttribute) {
            return $data;
        }

        if (gettype($data) === 'array' && isset($data['attributeType'])) {
            return $this->productManager->createAttribute($data['attributeType']);
        } elseif (gettype($data) === 'array' && isset($data['id'])) {
            return $this->productManager->getAttributeRepository()->find($data['id']);
        } else {
            return null;
        }
    }

    /**
     * Prepare data for binding to the form
     *
     * @param array $data Form data
     *
     * @return array Prepared form data
     */
    public function prepareFormData($data)
    {
        $optionTypes = array(
            'pim_catalog_multiselect',
            'pim_catalog_simpleselect'
        );

        // If the attribute type can have options but no options have been created,
        // create an empty option to render the corresponding form fields
        if (in_array($data['attributeType'], $optionTypes) && !isset($data['options'])) {
            $option = array(
                'optionValues' => array()
            );

            foreach ($this->localeManager->getActiveLocales() as $locale) {
                $option['optionValues'][] = array(
                    'locale' => $locale->getCode()
                );
            }

            $data['options'] = array($option);
        }

        return $data;
    }

    /**
     * Return an array of available attribute types
     *
     * @return array $types
     */
    public function getAttributeTypes()
    {
        $types = $this->productManager->getAttributeTypes();
        $choices = array();
        foreach ($types as $type) {
            $choices[$type] = $type;
        }
        asort($choices);

        return $choices;
    }

    /**
     * Make sure the ProductAttribute entity has the right backend properties
     *
     * @param ProductAttribute $attribute
     *
     * @return ProductAttribute $attribute
     */
    public function prepareBackendProperties(ProductAttribute $attribute)
    {
        $baseAttribute = $this->productManager->createAttribute($attribute->getAttributeType());

        $attribute->setBackendType($baseAttribute->getBackendType());
        $attribute->setBackendStorage($baseAttribute->getBackendStorage());

        return $attribute;
    }
}
