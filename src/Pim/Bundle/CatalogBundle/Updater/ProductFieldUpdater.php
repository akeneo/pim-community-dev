<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Adder\AdderRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Remover\RemoverRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;

/**
 * Provides basic operations to update a product field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFieldUpdater implements ProductFieldUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /** @var CopierRegistryInterface */
    protected $copierRegistry;

    /** @var AdderRegistryInterface */
    protected $adderRegistry;

    /** @var RemoverRegistryInterface */
    protected $removerRegistry;

    /**
     * @param AttributeRepositoryInterface $repository
     * @param SetterRegistryInterface      $setterRegistry
     * @param CopierRegistryInterface      $copierRegistry
     * @param AdderRegistryInterface       $adderRegistry
     * @param RemoverRegistryInterface     $removerRegistry
     */
    public function __construct(
        AttributeRepositoryInterface $repository,
        SetterRegistryInterface $setterRegistry,
        CopierRegistryInterface $copierRegistry,
        AdderRegistryInterface $adderRegistry,
        RemoverRegistryInterface $removerRegistry
    ) {
        $this->attributeRepository = $repository;
        $this->setterRegistry      = $setterRegistry;
        $this->copierRegistry      = $copierRegistry;
        $this->adderRegistry       = $adderRegistry;
        $this->removerRegistry     = $removerRegistry;
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
    public function removeData(ProductInterface $product, $field, $data, array $options = [])
    {
        $attribute = $this->getAttribute($field);
        if (null !== $attribute) {
            $remover = $this->removerRegistry->getAttributeRemover($attribute);
        } else {
            $remover = $this->removerRegistry->getFieldRemover($field);
        }

        if (null === $remover) {
            throw new \LogicException(sprintf('No remover found for field "%s"', $field));
        }

        if (null !== $attribute) {
            $remover->removeAttributeData($product, $attribute, $data, $options);
        } else {
            $remover->removeFieldData($product, $field, $data, $options);
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
