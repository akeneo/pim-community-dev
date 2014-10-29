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
    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ProductBuilder $builder
     */
    public function __construct(ProductBuilder $builder)
    {
        $this->productBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     *
     * TODO : first draft, lot of re-work / discuss to have here, about validation and concern
     */
    public function copyValue(
        array $products,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        $context = $this->validateContext(
            $fromAttribute,
            $toAttribute,
            $fromLocale,
            $toLocale,
            $fromScope,
            $toScope
        );

        // TODO reset if not useable locale or scope is passed, could be better to throw exception
        $fromLocale = ($fromAttribute->isLocalizable()) ? $fromLocale : null;
        $fromScope = ($fromAttribute->isScopable()) ? $fromScope : null;
        $toLocale = ($toAttribute->isLocalizable()) ? $toLocale : null;
        $toScope = ($toAttribute->isScopable()) ? $toScope : null;

        foreach ($products as $product) {
            $fromValue = $product->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
            $fromData = (null === $fromValue) ? '' : $fromValue->getData();
            $toValue = $product->getValue($toAttribute->getCode(), $toLocale, $toScope);
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
    public function supports(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $types = ['pim_catalog_text', 'pim_catalog_textarea'];
        $supportsFrom = in_array($fromAttribute->getAttributeType(), $types);
        $supportsTo = in_array($toAttribute->getAttributeType(), $types);

        return $supportsFrom && $supportsTo;
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
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     *
     * @throws \LogicException
     */
    protected function validateContext(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        // TODO check the existence of locale and scope used as options
        if ($fromAttribute->isLocalizable() && $fromLocale === null) {
            throw new \LogicException(sprintf('Locale expected for the attribute "%s"', $fromAttribute->getCode()));
        }
        if ($toAttribute->isLocalizable() && $toLocale === null) {
            throw new \LogicException(sprintf('Locale expected for the attribute "%s"', $toAttribute->getCode()));
        }
        if ($fromAttribute->isScopable() && $fromScope === null) {
            throw new \LogicException(sprintf('Scope expected for the attribute "%s"', $fromAttribute->getCode()));
        }
        if ($toAttribute->isScopable() && $toScope === null) {
            throw new \LogicException(sprintf('Scope expected for the attribute "%s"', $toAttribute->getCode()));
        }
    }
}
