<?php

namespace Akeneo\Pim\Structure\Component\Factory;

use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;

/**
 * Creates and configures an attribute instance.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeFactory implements SimpleFactoryInterface
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
    public function __construct(AttributeTypeRegistry $registry, string $attributeClass, string $productClass)
    {
        $this->registry = $registry;
        $this->attributeClass = $attributeClass;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): object
    {
        $attribute = new $this->attributeClass();
        $attribute->setEntityType($this->productClass);

        return $attribute;
    }

    /**
     * Create and configure an attribute
     *
     * @param string|null $type
     */
    public function createAttribute(?string $type = null): object
    {
        $attribute = $this->create();

        if (null !== $type && '' !== $type) {
            $attributeType = $this->registry->get($type);
            $attribute->setBackendType($attributeType->getBackendType());
            $attribute->setType($attributeType->getName());
            $attribute->setUnique($attributeType->isUnique());
        }

        return $attribute;
    }
}
