<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AdderRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;

/**
 * Provides basic operations to update a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements ProductUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /** @var CopierRegistryInterface */
    protected $copierRegistry;

    /** @var AdderRegistryInterface */
    protected $adderRegistry;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param SetterRegistryInterface      $setterRegistry
     * @param CopierRegistryInterface      $copierRegistry
     * @param AdderRegistryInterface       $adderRegistry
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
        SetterRegistryInterface $setterRegistry,
        CopierRegistryInterface $copierRegistry,
        AdderRegistryInterface $adderRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->setterRegistry = $setterRegistry;
        $this->copierRegistry = $copierRegistry;
        $this->adderRegistry = $adderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(ProductInterface $product, $field, $data, array $options = [])
    {
        $attribute = $this->getAttribute($field);
        if (null !== $attribute) {
            $setter = $this->setterRegistry->getAttributeSetter($attribute);
        } else {
            $setter = $this->setterRegistry->getFieldSetter($field);
        }

        if (null === $setter) {
            throw new \LogicException(sprintf('No setter found for field "%s"', $field));
        }

        if (null !== $attribute) {
            $setter->setAttributeData($product, $attribute, $data, $options);
        } else {
            $setter->setFieldData($product, $field, $data, $options);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addData(ProductInterface $product, $field, $data, array $options = [])
    {
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
     * {@inheritdoc}
     */
    public function copyData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        $fromField,
        $toField,
        array $options = []
    ) {
        $fromAttribute = $this->getAttribute($fromField);
        $toAttribute = $this->getAttribute($toField);
        if (null !== $fromAttribute && null !== $toAttribute) {
            $copier = $this->copierRegistry->getAttributeCopier($fromAttribute, $toAttribute);
        } else {
            $copier = $this->copierRegistry->getFieldCopier($fromField, $toField);
        }

        if (null === $copier) {
            throw new \LogicException(sprintf('No copier found for fields "%s" and "%s"', $fromField, $toField));
        }

        if (null !== $fromAttribute && null !== $toAttribute) {
            $copier->copyAttributeData($fromProduct, $toProduct, $fromAttribute, $toAttribute, $options);
        } else {
            $copier->copyFieldData($fromProduct, $toProduct, $fromField, $toField, $options);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null)
    {
        foreach ($products as $product) {
            $this->setData($product, $field, $data, ['locale' => $locale, 'scope' => $scope]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function copyValue(
        array $products,
        $fromField,
        $toField,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        $options = [
            'from_locale' => $fromLocale,
            'to_locale' => $toLocale,
            'from_scope' => $fromScope,
            'to_scope' => $toScope,
        ];
        foreach ($products as $product) {
            $this->copyData($product, $product, $fromField, $toField, $options);
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
