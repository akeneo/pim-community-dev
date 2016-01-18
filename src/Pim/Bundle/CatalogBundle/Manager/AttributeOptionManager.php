<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface;

/**
 * Attribute option manager
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
    protected $optionClass;

    /** @var string */
    protected $optionValueClass;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param string        $optionClass
     * @param string        $optionValueClass
     */
    public function __construct(
        ObjectManager $objectManager,
        $optionClass,
        $optionValueClass
    ) {
        $this->objectManager    = $objectManager;
        $this->optionClass      = $optionClass;
        $this->optionValueClass = $optionValueClass;
    }

    /**
     * Create an attribute option

     * @return AttributeOptionInterface
     */
    public function createAttributeOption()
    {
        $class = $this->optionClass;

        return new $class();
    }

    /**
     * Create an attribute option value
     *
     * @return AttributeOptionValueInterface
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
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return string
     */
    public function getAttributeOptionValueClass()
    {
        return $this->optionValueClass;
    }

    /**
     * Get an attribute option or throw an exception
     *
     * @param int $id
     *
     * @throws EntityNotFoundException
     *
     * @return AttributeInterface
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
