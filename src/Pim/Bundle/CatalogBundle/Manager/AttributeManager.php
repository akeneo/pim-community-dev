<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

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

    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var BulkSaverInterface */
    protected $optionSaver;

    /** @var AttributeRepositoryInterface */
    protected $repository;

    /** @var AttributeFactory */
    protected $factory;

    /**
     * Constructor
     *
     * @param string                       $attributeClass Attribute class
     * @param AttributeTypeRegistry        $registry       Attribute type registry
     * @param BulkSaverInterface           $optionSaver    Attribute option saver
     * @param AttributeRepositoryInterface $repository     Attribute repository
     * @param AttributeFactory             $factory        Attribute factory
     */
    public function __construct(
        $attributeClass,
        AttributeTypeRegistry $registry,
        BulkSaverInterface $optionSaver,
        AttributeRepositoryInterface $repository,
        AttributeFactory $factory
    ) {
        $this->attributeClass = $attributeClass;
        $this->registry       = $registry;
        $this->optionSaver    = $optionSaver;
        $this->repository     = $repository;
        $this->factory        = $factory;
    }

    /**
     * Create an attribute
     *
     * @param string $type
     *
     * @return AttributeInterface
     *
     * @deprecated will be removed in 1.5, please use AttributeFactory::createAttribute
     */
    public function createAttribute($type = null)
    {
        return $this->factory->createAttribute($type);
    }

    /**
     * Get the attribute FQCN
     *
     * @return string
     *
     * @deprecated will be removed in 1.5 please use %pim_catalog.entity.attribute.class%
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
        }
        $this->optionSaver->saveAll($attribute->getOptions()->toArray());
    }

    /**
     * Get an attribute or throw an exception
     *
     * @param int $id
     *
     * @throws EntityNotFoundException
     *
     * @return AttributeInterface
     *
     * @deprecated will be removed in 1.5 please use AttributeRepositoryInterface->find()
     */
    public function getAttribute($id)
    {
        $attribute = $this->repository->find($id);

        if (null === $attribute) {
            throw new EntityNotFoundException();
        }

        return $attribute;
    }
}
