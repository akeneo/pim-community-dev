<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Sets a property of a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertySetter implements PropertySetterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param SetterRegistryInterface               $setterRegistry
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SetterRegistryInterface $setterRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->setterRegistry = $setterRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface|ProductModelInterface $entity
     */
    public function setData($entity, $field, $data, array $options = [])
    {
        $setter = $this->setterRegistry->getSetter($field);
        if (null === $setter) {
            throw UnknownPropertyException::unknownProperty($field);
        }

        if ($setter instanceof AttributeSetterInterface) {
            $attribute = $this->getAttribute($field);
            $setter->setAttributeData($entity, $attribute, $data, $options);
        } elseif ($setter instanceof FieldSetterInterface) {
            $setter->setFieldData($entity, $field, $data, $options);
        } else {
            throw UnknownPropertyException::unknownProperty($field);
        }

        return $this;
    }

    /**
     * @param string $code
     *
     * @return null|AttributeInterface
     * @throws ResourceNotFoundException
     */
    protected function getAttribute($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        if (null === $attribute) {
            throw new ResourceNotFoundException(AttributeInterface::class);
        }

        return $attribute;
    }
}
