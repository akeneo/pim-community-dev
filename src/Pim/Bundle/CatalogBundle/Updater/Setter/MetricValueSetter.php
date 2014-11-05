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
        $this->types          = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * setValue( '12 KILOGRAM'
     *           ['data' => 12, 'unit' => 'kg']
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        //TODO: pmd is to hight need to split the method
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('$data have to be an array');
        }

        if (!array_key_exists('data', $data)) {
            throw new \LogicException('Missing "data" key in array');
        }

        if (!array_key_exists('unit', $data)) {
            throw new \LogicException('Missing "unit" key in array');
        }

        if (!is_numeric($data['data']) || !is_string($data['unit'])) {
            throw new \LogicException('Invalid data type or invalid unit type');
        }

        if (!$this->measureManager->unitExistsInFamily($data['unit'], $attribute->getMetricFamily())) {
            throw new \LogicException(
                sprintf('"%s" does not exist in any attribute\'s families', $data['unit'])
            );
        }

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
}
