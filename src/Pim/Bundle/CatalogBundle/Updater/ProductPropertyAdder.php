<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AdderRegistryInterface;

/**
 * Adds a data in the property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertyAdder implements PropertyAdderInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AdderRegistryInterface */
    protected $adderRegistry;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param AdderRegistryInterface       $adderRegistry
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
        AdderRegistryInterface $adderRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->adderRegistry       = $adderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function addData($product, $field, $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }

        $attribute = $this->getAttribute($field);
        if (null !== $attribute) {
            $adder = $this->adderRegistry->getAttributeAdder($attribute);
        } else {
            $adder = $this->adderRegistry->getFieldAdder($field);
        }

        if (null === $adder) {
            throw new \LogicException(sprintf('No adder found for field "%s"', $field));
        }

        if (null !== $attribute) {
            $adder->addAttributeData($product, $attribute, $data, $options);
        } else {
            $adder->addFieldData($product, $field, $data, $options);
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
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);

        return $attribute;
    }
}
