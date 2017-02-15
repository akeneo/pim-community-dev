<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface;
use Pim\Component\Catalog\Updater\Setter\SetterRegistryInterface;

/**
 * Sets a property of a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertySetter implements PropertySetterInterface
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
    public function setData($product, $field, $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                ProductInterface::class
            );
        }

        $setter = $this->setterRegistry->getSetter($field);
        if (null === $setter) {
            throw UnknownPropertyException::unknownProperty($field);
        }

        if ($setter instanceof AttributeSetterInterface) {
            $attribute = $this->getAttribute($field);
            $setter->setAttributeData($product, $attribute, $data, $options);
        } else {
            $setter->setFieldData($product, $field, $data, $options);
        }

        return $this;
    }

    /**
     * @param string $code
     *
     * @return AttributeInterface|null
     */
    protected function getAttribute($code)
    {
        return $this->attributeRepository->findOneByIdentifier($code);
    }
}
