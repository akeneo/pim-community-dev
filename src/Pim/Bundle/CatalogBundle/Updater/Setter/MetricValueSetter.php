<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
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
    protected $factory;

    /** @var MeasureManager */
    protected $measureManager;

    /**
     * @param ProductBuilder $builder
     * @param MetricFactory  $factory
     * @param MeasureManager $measureManager
     * @param array          $supportedTypes
     */
    public function __construct(
        ProductBuilder $builder,
        MetricFactory $factory,
        MeasureManager $measureManager,
        array $supportedTypes
    ) {
        $this->productBuilder = $builder;
        $this->factory        = $factory;
        $this->measureManager = $measureManager;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * setValue( '12 KILOGRAM'
     *           ['data' => 12, 'unit' => 'kg']
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        $this->checkData($attribute, $data);

        $unit = $data['unit'];
        $data = $data['data'];

        $fullUnitName = $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily());
        $fullUnitName = array_flip($fullUnitName);
        $fullUnitName = $fullUnitName[$unit];

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }
            $metric = $this->factory->createMetric($attribute->getMetricFamily());
            $metric->setUnit($fullUnitName);
            $metric->setData($data);

            $value->setMetric($metric);
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

        if (!$this->measureManager->unitExistsInFamily($data['unit'], $attribute->getMetricFamily())) {
            throw InvalidArgumentException::arrayInvalidKey(
                $attribute->getCode(),
                'unit',
                sprintf('"%s" does not exist in any attribute\'s families', $data['unit']),
                'setter',
                'metric'
            );
        }
    }
}
