<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
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

    /** @var MeasureManager */
    protected $measureManager;

    /**
     * @param ProductBuilder $builder
     * @param MetricFactory  $factory
     */
    public function __construct(ProductBuilder $builder, MetricFactory $factory, MeasureManager $measureManager)
    {
        $this->productBuilder = $builder;
        $this->factory = $factory;
        $this->measureManager = $measureManager;
    }

    /**
     * {@inheritdoc}
     *
     * setValue( '12 KILOGRAM'
     *           ['data' => 12, 'unit' => 'KILOGRAM']
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        //TODO: pmd is to hight need to split the method
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        if (!is_array($data)) {
            throw new \LogicException('$data have to be an array');
        }

        if (!array_key_exists('data', $data)) {
            throw new \LogicException('Missing "data" key in array');
        }

        if (!array_key_exists('unit', $data)) {
            throw new \LogicException('Missing "unit" key in array');
        }

        if (!is_numeric($data['data'])) {
            throw new \LogicException(sprintf('Attribute "%s" expects a numeric data', $attribute->getCode()));
        }

        if (!is_string($data['unit'])) {
            throw new \LogicException(sprintf('Attribute "%s" expects a string unit', $attribute->getCode()));
        }

        if (!$this->measureManager->unitExistInFamily($data['unit'], $attribute->getMetricFamily())) {
            throw new \LogicException(
                sprintf('"%s" does not exist in any attribute\'s families', $data['unit'])
            );
        }

        $unit = $data['unit'];
        $data = $data['data'];

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }
            //Todo: change this cases because there is a case where metric is not setted
            if (null === $metric = $value->getMetric()) {
                $metric = $this->factory->createMetric($attribute->getMetricFamily());
                $metric->setUnit($unit);
                $metric->setData($data);

                $value->setMetric($metric);
            }
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
