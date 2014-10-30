<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;

/**
 * Sets a metric value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricValueSetter implements SetterInterface
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var MetricFactory */
    protected $factory;

    /**
     * @param ProductBuilder $builder
     * @param MetricFactory  $factory
     */
    public function __construct(ProductBuilder $builder, MetricFactory $factory)
    {
        $this->productBuilder = $builder;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * setValue( '12 KG'
     *           ['data' => 12, 'unit' => 'KG']
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        // TODO : check the unit belongs to the family
        // TODO : check the data is a number

        $unit = $data['unit'];
        $data = $data['data'];

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }
            if (null === $metric = $value->getMetric()) {
                $metric = $this->factory->createMetric($attribute->getMetricFamily());
                $value->setMetric($metric);
            }
            $metric->setUnit($unit);
            $metric->setData($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $attribute)
    {
        $types = ['pim_catalog_metric'];

        return in_array($attribute->getAttributeType(), $types);
    }
}
