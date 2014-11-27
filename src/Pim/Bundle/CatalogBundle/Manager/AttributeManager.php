<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
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
class AttributeManager implements SaverInterface, BulkSaverInterface, RemoverInterface
{
    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $productClass;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param string                   $attributeClass  Attribute class
     * @param string                   $productClass    Product class
     * @param ObjectManager            $objectManager   Object manager
     * @param AttributeTypeRegistry    $registry        Attribute type registry
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function __construct(
        $attributeClass,
        $productClass,
        ObjectManager $objectManager,
        AttributeTypeRegistry $registry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->attributeClass  = $attributeClass;
        $this->productClass    = $productClass;
        $this->objectManager   = $objectManager;
        $this->registry        = $registry;
        $this->eventDispatcher = $eventDispatcher;
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
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Model\AttributeInterface, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->persist($object);
        if (true === $options['flush']) {
            $this->objectManager->flush($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        $options = array_merge(['flush' => true], $options);
        foreach ($objects as $object) {
            $this->save($object, ['flush' => false]);
        }

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Model\AttributeInterface, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->eventDispatcher->dispatch(AttributeEvents::PRE_REMOVE, new GenericEvent($object));

        $this->objectManager->remove($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(AttributeEvents::POST_REMOVE, new GenericEvent($object));
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
