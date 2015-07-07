<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Copy a metric value attribute in other metric value attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricValueCopier extends AbstractValueCopier
{
    /** @var MetricFactory */
    protected $metricFactory;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param MetricFactory            $metricFactory
     * @param array                    $supportedFromTypes
     * @param array                    $supportedToTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        MetricFactory $metricFactory,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->metricFactory  = $metricFactory;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
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
        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope, 'base');
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope, 'base');
        $this->attrValidatorHelper->validateUnitFamilies($fromAttribute, $toAttribute);

        foreach ($products as $product) {
            $this->copySingleValue(
                $product,
                $fromAttribute,
                $toAttribute,
                $fromLocale,
                $toLocale,
                $fromScope,
                $toScope
            );
        }
    }

    /**
     * @param ProductInterface   $product
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     */
    protected function copySingleValue(
        ProductInterface $product,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $product->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $fromData = $fromValue->getData();
            $toValue = $product->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($product, $toAttribute, $toLocale, $toScope);
            }

            if (null === $metric = $toValue->getMetric()) {
                $metric = $this->metricFactory->createMetric($fromData->getFamily());
            }

            $metric->setUnit($fromData->getUnit());
            $metric->setData($fromData->getData());

            $toValue->setMetric($metric);
        }
    }
}
