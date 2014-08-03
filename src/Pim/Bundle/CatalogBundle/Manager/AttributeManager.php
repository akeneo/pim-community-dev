<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;

/**
 * Attribute manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeManager
{
    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $optionClass;

    /** @var string */
    protected $optionValueClass;

    /** @var string */
    protected $productClass;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var AttributeTypeFactory */
    protected $factory;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param string                   $attributeClass   Attribute class
     * @param string                   $optionClass      Option class
     * @param string                   $optionValueClass Option value class
     * @param string                   $productClass     Product class
     * @param ObjectManager            $objectManager    Object manager
     * @param AttributeTypeFactory     $factory          Attribute type factory
     * @param EventDispatcherInterface $eventDispatcher  Event dispatcher
     */
    public function __construct(
        $attributeClass,
        $optionClass,
        $optionValueClass,
        $productClass,
        ObjectManager $objectManager,
        AttributeTypeFactory $factory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->attributeClass   = $attributeClass;
        $this->optionClass      = $optionClass;
        $this->optionValueClass = $optionValueClass;
        $this->productClass     = $productClass;
        $this->objectManager    = $objectManager;
        $this->factory          = $factory;
        $this->eventDispatcher  = $eventDispatcher;
    }

    /**
     * Create an attribute
     *
     * @param string $type
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AbstractAttribute
     */
    public function createAttribute($type = null)
    {
        $class = $this->getAttributeClass();
        $attribute = new $class();
        $attribute->setEntityType($this->productClass);

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
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOption
     */
    public function createAttributeOption()
    {
        $class = $this->optionClass;

        return new $class();
    }

    /**
     * Create an attribute option value
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue
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

    /**
     * Remove an attribute
     *
     * @param AbstractAttribute $attribute
     */
    public function remove(AbstractAttribute $attribute)
    {
        $this->eventDispatcher->dispatch(AttributeEvents::PRE_REMOVE, new GenericEvent($attribute));

        $this->objectManager->remove($attribute);
        $this->objectManager->flush();
    }
}
