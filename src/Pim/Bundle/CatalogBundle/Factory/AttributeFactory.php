<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;

/**
 * Attribute factory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeFactory
{
    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $productClass;

    /**
     * @param AttributeTypeRegistry $registry
     * @param string                $attributeClass
     * @param string                $productClass
     */
    public function __construct(AttributeTypeRegistry $registry, $attributeClass, $productClass)
    {
        $this->registry       = $registry;
        $this->attributeClass = $attributeClass;
        $this->productClass   = $productClass;
    }

    /**
     * Create and configure an attribute
     *
     * @param string|null $type
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface
     */
    public function createAttribute($type = null)
    {
        $attribute = new $this->attributeClass();

        $attribute->setEntityType($this->productClass);

        if ($type) {
            $attributeType = $this->registry->get($type);
            $attribute->setBackendType($attributeType->getBackendType());
            $attribute->setAttributeType($attributeType->getName());
        }

        return $attribute;
    }
}
