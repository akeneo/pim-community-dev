<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;

/**
 * Copy a text value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextValueCopier implements CopierInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param AttributeRepository $repository
     * @param ProductBuilder      $builder
     */
    public function __construct(AttributeRepository $repository, ProductBuilder $builder)
    {
        $this->attributeRepository = $repository;
        $this->productBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     *
     * TODO : first draft, lot of re-work / discuss to have here, about validation and concern
     */
    public function copyValue(
        array $products,
        $fromField,
        $toField,
        $fromLocale = null,
        $fromScope = null,
        $toLocale = null,
        $toScope = null
    ) {
        $fromAttribute = $this->attributeRepository->findOneByCode($fromField);
        if (!$fromAttribute) {
            throw new \LogicException(sprintf('Attribute "%s" not exists', $fromField));
        }
        $toAttribute = $this->attributeRepository->findOneByCode($toField);
        if (!$toAttribute) {
            throw new \LogicException(sprintf('Attribute "%s" not exists', $toField));
        }

        $context = $this->validateContext(
            $fromAttribute,
            $toAttribute,
            $fromLocale,
            $fromScope,
            $toLocale,
            $toScope
        );

        // TODO reset if not useable locale or scope is passed, could be better to throw exception
        $fromLocale = ($fromAttribute->isLocalizable()) ? $fromLocale : null;
        $fromScope = ($fromAttribute->isScopable()) ? $fromScope : null;
        $toLocale = ($toAttribute->isLocalizable()) ? $toLocale : null;
        $toScope = ($toAttribute->isScopable()) ? $toScope : null;

        foreach ($products as $product) {
            $fromValue = $product->getValue($fromField, $fromLocale, $fromScope);
            $fromData = (null === $fromValue) ? '' : $fromValue->getData();
            $toValue = $product->getValue($toField, $toLocale, $toScope);
            if (null === $toValue) {
                // TODO : not sure about the relevancy of product builder for this kind of operation
                $toValue = $this->productBuilder->addProductValue($product, $toAttribute, $toLocale, $toScope);
            }
            $toValue->setData($fromData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($fromField, $toField)
    {
        $types = ['pim_catalog_text', 'pim_catalog_textarea'];

        return in_array($fromField, $types) && in_array($toField, $types);
    }

    /**
     * Validate the data
     *
     * @param string $data
     */
    protected function validateData($data)
    {
        if (!is_string($data)) {
            throw new \LogicException('A string is expected');
        }
    }

    /**
     * Validate the context
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $fromScope
     * @param string             $toLocale
     * @param string             $toScope
     *
     * @throws \LogicException
     */
    protected function validateContext(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $fromScope,
        $toLocale,
        $toScope
    ) {
        // TODO check the existence of locale and scope used as options
        if ($fromAttribute->isLocalizable() && $fromLocale === null) {
            throw new \LogicException(sprintf('A locale is expected for field %s', $fromAttribute->getCode()));
        }
        if ($toAttribute->isLocalizable() && $toLocale === null) {
            throw new \LogicException(sprintf('A locale is expected for field %s', $toAttribute->getCode()));
        }
        if ($fromAttribute->isScopable() && $fromScope === null) {
            throw new \LogicException(sprintf('A scope is expected for field %s', $fromAttribute->getCode()));
        }
        if ($toAttribute->isScopable() && $toScope === null) {
            throw new \LogicException(sprintf('A scope is expected for field %s', $toAttribute->getCode()));
        }
    }
}
