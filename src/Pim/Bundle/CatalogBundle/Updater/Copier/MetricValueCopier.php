<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
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
    protected $factory;

    /**
     * @param ProductBuilder $builder
     * @param MetricFactory  $factory
     * @param array          $supportedTypes
     */
    public function __construct(ProductBuilder $builder, MetricFactory $factory, array $supportedTypes)
    {
        parent::__construct(
            $builder,
            $supportedTypes
        );
        $this->factory = $factory;
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

        foreach ($products as $product) {
            $fromValue = $product->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
            $fromData = (null === $fromValue) ? '' : $fromValue->getData();
            $toValue = $product->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($product, $toAttribute, $toLocale, $toScope);
            }
            if ($fromData instanceof MetricInterface) {
                if ($fromData->getFamily() === $toValue->getData()->getFamily()) {
                    $metric = $this->factory->createMetric($fromData->getFamily());

                    $metric->setUnit($fromData->getUnit());
                    $metric->setData($fromData->getData());

                    $toValue->setMetric($metric);
                } else {
                    throw new \InvalidArgumentException();
                }
            }
        }
    }
}
