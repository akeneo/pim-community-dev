<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetterRegistry implements SetterRegistryInterface
{
    /** @var AttributeSetterInterface[] priorized attribute setters */
    protected array $attributeSetters = [];

    /** @var FieldSetterInterface[] priorized field setters */
    protected array $fieldSetters = [];

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param iterable<SetterInterface> $propertySetters
     */
    public function __construct(
        protected IdentifiableObjectRepositoryInterface $attributeRepository,
        iterable $propertySetters
    ) {
        foreach ($propertySetters as $propertySetter) {
            $this->register($propertySetter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(SetterInterface $setter): self
    {
        if ($setter instanceof FieldSetterInterface) {
            $this->fieldSetters[] = $setter;
        }
        if ($setter instanceof AttributeSetterInterface) {
            $this->attributeSetters[] = $setter;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetter(string $property): ?SetterInterface
    {
        $attribute = $this->getAttribute($property);
        if (null !== $attribute) {
            $setter = $this->getAttributeSetter($attribute);
        } else {
            $setter = $this->getFieldSetter($property);
        }

        return $setter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldSetter(string $field): ?FieldSetterInterface
    {
        foreach ($this->fieldSetters as $setter) {
            if ($setter->supportsField($field)) {
                return $setter;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetter(AttributeInterface $attribute): ?AttributeSetterInterface
    {
        foreach ($this->attributeSetters as $setter) {
            if ($setter->supportsAttribute($attribute)) {
                return $setter;
            }
        }

        return null;
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute(string $code): ?AttributeInterface
    {
        return $this->attributeRepository->findOneByIdentifier($code);
    }
}
