<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;

/**
 * Attribute manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeManager
{
    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * @var string
     */
    protected $optionClass;

    /**
     * @var string
     */
    protected $optionValueClass;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param string               $attributeClass   Attribute class
     * @param string               $optionClass      Option class
     * @param string               $optionValueClass Option value class
     * @param string               $productClass     Product class
     * @param ObjectManager        $objectManager    Object manager
     * @param AttributeTypeFactory $factory          Attribute type factory
     */
    public function __construct(
        $attributeClass,
        $optionClass,
        $optionValueClass,
        $productClass,
        ObjectManager $objectManager,
        AttributeTypeFactory $factory
    ) {
        $this->attributeClass   = $attributeClass;
        $this->optionClass      = $optionClass;
        $this->optionValueClass = $optionValueClass;
        $this->productClass     = $productClass;
        $this->objectManager    = $objectManager;
        $this->factory          = $factory;
    }

    /**
     * Create an attribute
     *
     * @param string $type
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    public function createAttribute($type = null)
    {
        $class = $this->getAttributeClass();
        $attribute = new $class();
        $attribute->setEntityType($this->productClass);

        $attribute->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
        if ($type) {
            $attributeType = $this->factory->get($type);
            $attribute->setBackendType($attributeType->getBackendType());
            $attribute->setAttributeType($attributeType->getName());
        }

        return $attribute;
    }

    /**
     * Create an attribute option
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption
     */
    public function createAttributeOption()
    {
        $class = $this->optionClass;

        return new $class();
    }

    /**
     * Create an attribute option value
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOptionValue
     */
    public function createAttributeOptionValue()
    {
        $class = $this->optionValueClass;

        return new $class();
    }

    /**
     * Get the attribute FQCN
     *
     * @return string
     */
    public function getAttributeClass()
    {
        return $this->attributeClass;
    }

    /**
     * Get the attribute option FQCN
     *
     * @return string
     */
    public function getAttributeOptionClass()
    {
        return $this->optionClass;
    }

    /**
     * Get a list of available attribute types
     *
     * @return string[]
     */
    public function getAttributeTypes()
    {
        $types = $this->factory->getAttributeTypes($this->productClass);
        $choices = array();
        foreach ($types as $type) {
            $choices[$type] = $type;
        }
        asort($choices);

        return $choices;
    }
}
