<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Bundle\Sql\AttributeInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
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
    /** @var LruArrayAttributeRepository */
    protected $attributeRepository;

    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /**
     * @param LruArrayAttributeRepository $repository
     * @param SetterRegistryInterface     $setterRegistry
     */
    public function __construct(
        LruArrayAttributeRepository $repository,
        SetterRegistryInterface $setterRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->setterRegistry = $setterRegistry;
    }

    /**
     * {@inheritdoc}
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
        } else {
            $setter->setFieldData($entity, $field, $data, $options);
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
