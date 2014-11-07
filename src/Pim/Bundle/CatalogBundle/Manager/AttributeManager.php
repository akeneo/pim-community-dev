<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Attribute manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeManager implements ResourceManagerInterface
{
    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $productClass;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var AttributeTypeFactory */
    protected $factory;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ResourceManagerInterface */
    protected $resourceManager;

    /**
     * Constructor
     *
     * @param string                   $attributeClass  Attribute class
     * @param string                   $productClass    Product class
     * @param ObjectManager            $objectManager   Object manager
     * @param AttributeTypeFactory     $factory         Attribute type factory
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     * @param ResourceManagerInterface $resourceManager Resource manager
     */
    public function __construct(
        $attributeClass,
        $productClass,
        ObjectManager $objectManager,
        AttributeTypeFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        ResourceManagerInterface $resourceManager
    ) {
        $this->attributeClass  = $attributeClass;
        $this->productClass    = $productClass;
        $this->objectManager   = $objectManager;
        $this->factory         = $factory;
        $this->eventDispatcher = $eventDispatcher;
        $this->resourceManager = $resourceManager;
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
     * Get the attribute FQCN
     *
     * @return string
     */
    public function getAttributeClass()
    {
        return $this->attributeClass;
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

    /**
     * Update attribute option sorting
     *
     * @param AttributeInterface $attribute
     * @param array              $sorting
     */
    public function updateSorting(AttributeInterface $attribute, array $sorting = [])
    {
        foreach ($attribute->getOptions() as $option) {
            if (isset($sorting[$option->getId()])) {
                $option->setSortOrder($sorting[$option->getId()]);
            } else {
                $option->setSortOrder(0);
            }

            $this->objectManager->persist($option);
        }

        $this->objectManager->flush();
    }

    /**
     * Get an attribute or throw an exception
     * @param integer $id
     *
     * @return AttributeInterface
     * @throws EntityNotFoundException
     */
    public function getAttribute($id)
    {
        $attribute = $this->objectManager->find($this->getAttributeClass(), $id);

        if (null === $attribute) {
            throw new EntityNotFoundException();
        }

        return $attribute;
    }
}
