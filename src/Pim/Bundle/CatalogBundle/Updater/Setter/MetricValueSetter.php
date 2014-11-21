<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;

/**
 * Sets a metric value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricValueSetter extends AbstractValueSetter
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var MetricFactory */
    protected $metricFactory;

    /** @var MeasureManager */
    protected $measureManager;

    /**
     * @param ProductBuilder $productBuilder
     * @param MetricFactory  $metricFactory
     * @param MeasureManager $measureManager
     * @param array          $supportedTypes
     */
    public function __construct(
        ProductBuilder $productBuilder,
        MetricFactory $metricFactory,
        MeasureManager $measureManager,
        array $supportedTypes
    ) {
        $this->productBuilder = $productBuilder;
        $this->metricFactory  = $metricFactory;
        $this->measureManager = $measureManager;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * setValue( [$products], $weightAttribute,
     *           ['data' => 12, 'unit' => 'KILOGRAM']
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        $this->checkData($attribute, $data);

        $unit = $data['unit'];
        $data = $data['data'];

        foreach ($products as $product) {
            $this->setData($attribute, $product, $data, $unit, $locale, $scope);
        }
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'setter', 'metric');
        }

        if (!array_key_exists('data', $data)) {
            throw InvalidArgumentException::arrayKeyExpected($attribute->getCode(), 'data', 'setter', 'metric');
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidArgumentException::arrayKeyExpected($attribute->getCode(), 'unit', 'setter', 'metric');
        }

        if (!is_numeric($data['data'])) {
            throw InvalidArgumentException::arrayNumericKeyExpected($attribute->getCode(), 'data', 'setter', 'metric');
        }

        if (!is_string($data['unit'])) {
            throw InvalidArgumentException::arrayStringKeyExpected($attribute->getCode(), 'unit', 'setter', 'metric');
        }

        if (!array_key_exists(
            $data['unit'],
            $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())
        )) {
            throw InvalidArgumentException::arrayInvalidKey(
                $attribute->getCode(),
                'unit',
                sprintf('"%s" does not exist in any attribute\'s families', $data['unit']),
                'setter',
                'metric'
            );
        }
    }

    /**
     * Set the data into the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param mixed              $data
     * @param string             $unit
     * @param string             $locale
     * @param string             $scope
     */
    protected function setData(
        AttributeInterface $attribute,
        ProductInterface $product,
        $data,
        $unit,
        $locale,
        $scope
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (null === $metric = $value->getMetric()) {
            $metric = $this->metricFactory->createMetric($attribute->getMetricFamily());
        }

        $value->setMetric($metric);
        $metric->setUnit($unit);
        $metric->setData($data);
    }
}
