<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;

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
     * @param ProductBuilder $productBuilder
     * @param MetricFactory  $metricFactory
     * @param array          $supportedTypes
     */
    public function __construct(ProductBuilder $productBuilder, MetricFactory $metricFactory, array $supportedTypes)
    {
        parent::__construct(
            $productBuilder,
            $supportedTypes
        );
        $this->metricFactory = $metricFactory;
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
        AttributeUtility::validateLocale($fromAttribute, $fromLocale);
        AttributeUtility::validateScope($fromAttribute, $fromScope);
        AttributeUtility::validateLocale($toAttribute, $toLocale);
        AttributeUtility::validateScope($toAttribute, $toScope);
        AttributeUtility::validateUnitFamilyFromAttribute($fromAttribute, $toAttribute);

        foreach ($products as $product) {
            $this->copySingleValue(
                $fromAttribute,
                $toAttribute,
                $fromLocale,
                $toLocale,
                $fromScope,
                $toScope,
                $product
            );
        }
    }

    /**
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     * @param string             $product
     */
    protected function copySingleValue(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope,
        $product
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
