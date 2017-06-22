<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface;
use Pim\Component\Catalog\Updater\Setter\SetterRegistryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithValuesPropertySetter implements PropertySetterInterface
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
     */
    public function setData($entityWithValues, $field, $data, array $options = [])
    {
        if (!$entityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithValues),
                EntityWithValuesInterface::class
            );
        }

        $setter = $this->setterRegistry->getSetter($field);
        if (null === $setter) {
            throw UnknownPropertyException::unknownProperty($field);
        }

        if (!$setter instanceof AttributeSetterInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithValues),
                AttributeSetterInterface::class
            );
        }

        $attribute = $this->getAttribute($field);
        $setter->setAttributeData($entityWithValues, $attribute, $data, $options);

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
