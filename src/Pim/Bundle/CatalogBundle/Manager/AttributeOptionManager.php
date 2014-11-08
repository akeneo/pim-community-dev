<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Component\Resource\Model\UpdaterInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Event\AttributeOptionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Attribute option manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionManager implements UpdaterInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $optionClass;

    /** @var string */
    protected $optionValueClass;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $optionClass
     * @param string                   $optionValueClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $optionClass,
        $optionValueClass
    ) {
        $this->objectManager    = $objectManager;
        $this->eventDispatcher  = $eventDispatcher;
        $this->optionClass      = $optionClass;
        $this->optionValueClass = $optionValueClass;
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
     * Get the attribute option FQCN
     *
     * @return string
     */
    public function getAttributeOptionClass()
    {
        return $this->optionClass;
    }

    /**
     * Get the attribute option value FQCN
     *
     * @return string
     */
    public function getAttributeOptionValueClass()
    {
        return $this->optionValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function update($object, array $options = [])
    {
        if (!$object instanceof AttributeOption) {
            throw new \InvalidArgumentException(
                sprintf('Expects a AttributeOption, "%s" provided', get_class($object))
            );
        }

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
