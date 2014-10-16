<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Event\AttributeOptionEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Attribute manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionManager
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $attributeOptionClass;

    /** @var string */
    protected $attributeOptionValueClass;

    /**
     * Constructor
     *
     * @param ObjectManager   $objectManager
     * @param EventDispatcher $eventDispatcher
     * @param string          $attributeOptionClass
     * @param string          $attributeOptionValueClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcher $eventDispatcher,
        $attributeOptionClass,
        $attributeOptionValueClass
    ) {
        $this->objectManager             = $objectManager;
        $this->eventDispatcher           = $eventDispatcher;
        $this->attributeOptionClass      = $attributeOptionClass;
        $this->attributeOptionValueClass = $attributeOptionValueClass;
    }

    /**
     * Create an attribute option
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOption
     */
    public function createAttributeOption()
    {
        $class = $this->attributeOptionClass;

        return new $class();
    }

    /**
     * Create an attribute option value
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue
     */
    public function createAttributeOptionValue()
    {
        $class = $this->attributeOptionValueClass;

        return new $class();
    }

    /**
     * Get the attribute option FQCN
     *
     * @return string
     */
    public function getAttributeOptionClass()
    {
        return $this->attributeOptionClass;
    }

    /**
     * Get the attribute option value FQCN
     *
     * @return string
     */
    public function getAttributeOptionValueClass()
    {
        return $this->attributeOptionValueClass;
    }

    /**
     * Update an attribute option
     *
     * @param AttributeOption $attributeOption
     */
    public function update(AttributeOption $attributeOption)
    {
        $this->objectManager->persist($attributeOption);
        $this->objectManager->flush($attributeOption);
    }

    /**
     * Remove an attribute option
     *
     * @param AttributeOption $attributeOption
     */
    public function remove(AttributeOption $attributeOption)
    {
        $this->eventDispatcher->dispatch(AttributeOptionEvents::PRE_REMOVE, new GenericEvent($attributeOption));

        $this->objectManager->remove($attributeOption);
        $this->objectManager->flush($attributeOption);
    }

    /**
     * Get an attribute option or throw an exception
     * @param integer $id
     *
     * @return AttributeInterface
     * @throws EntityNotFoundException
     */
    public function getAttributeOption($id)
    {
        $attribute = $this->objectManager->find($this->getAttributeOptionClass(), $id);

        if (null === $attribute) {
            throw new EntityNotFoundException();
        }

        return $attribute;
    }
}
