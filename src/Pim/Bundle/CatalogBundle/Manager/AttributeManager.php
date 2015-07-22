<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
    protected $productClass;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var AttributeTypeRegistry */
    protected $registry;

    /**
     * Constructor
     *
     * @param string                $attributeClass Attribute class
     * @param string                $productClass   Product class
     * @param ObjectManager         $objectManager  Object manager
     * @param AttributeTypeRegistry $registry       Attribute type registry
     */
    public function __construct(
        $attributeClass,
        $productClass,
        ObjectManager $objectManager,
        AttributeTypeRegistry $registry
    ) {
        $this->attributeClass  = $attributeClass;
        $this->productClass    = $productClass;
        $this->objectManager   = $objectManager;
        $this->registry        = $registry;
    }

    /**
     * Create an attribute
     *
     * @param string $type
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface
     */
    public function createAttribute($type = null)
    {
        $class = $this->getAttributeClass();
        $attribute = new $class();
        $attribute->setEntityType($this->productClass);

        if ($type) {
            $attributeType = $this->registry->get($type);
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
        $types = $this->registry->getAliases();
        $choices = array();
        foreach ($types as $type) {
            $choices[$type] = $type;
        }
        asort($choices);

        return $choices;
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
     *
     * @param integer $id
     *
     * @throws EntityNotFoundException
     *
     * @return AttributeInterface
     */
    public function getAttribute($id)
    {
        $attribute = $this->objectManager->find($this->getAttributeClass(), $id);

        if (null === $attribute) {
            throw new EntityNotFoundException();
        }

        return $attribute;
    }

    /**
     * Remove an attribute
     *
     * @param AttributeInterface $attribute
     *
     * @deprecated will be removed in 1.4, replaced by AttributeRemover::remove
     */
    public function remove(AttributeInterface $attribute)
    {
        $this->objectManager->remove($attribute);
        $this->objectManager->flush();
    }
}
