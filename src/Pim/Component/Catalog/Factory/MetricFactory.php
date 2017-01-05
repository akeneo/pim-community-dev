<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Exception\MeasureException;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Component\Catalog\Model\MetricInterface;

/**
 * Creates and configures a metric instance.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFactory
{
    /** @var MeasureConverter */
    protected $measureConverter;

    /** @var MeasureManager */
    protected $measureManager;

    /** @var string */
    protected $metricClass;

    /**
     * @param MeasureConverter $measureConverter
     * @param MeasureManager   $measureManager
     * @param string           $metricClass
     */
    public function __construct(MeasureConverter $measureConverter, MeasureManager $measureManager, $metricClass)
    {
        $this->measureConverter = $measureConverter;
        $this->measureManager = $measureManager;
        $this->metricClass = $metricClass;
    }

    /**
     * @param string $family
     * @param string $unit
     * @param double $data
     *
     * @throws \InvalidArgumentException
     * @return MetricInterface
     */
    public function createMetric($family, $unit, $data)
    {
        $baseData = null !== $data ? $this->convertDataToBaseData($family, $unit, $data) : null;
        $baseUnit = $this->getBaseUnit($family);

        $metric = new $this->metricClass($family, $unit, $data, $baseUnit, $baseData);

        return $metric;
    }

    /**
     * Converts the provided data to the metric base data, according to its family standard unit (base unit).
     *
     * @param string $family
     * @param string $unit
     * @param double $data
     *
     * @throws \LogicException
     * @return double
     */
    protected function convertDataToBaseData($family, $unit, $data)
    {
        try {
            $convertedData = $this->measureConverter->setFamily($family)->convertBaseToStandard($unit, $data);
        } catch (MeasureException $e) {
            // TODO: This should be a dedicated exception. Will be addressed in a coming PR.
            throw new \LogicException(
                sprintf(
                    'Metric data %d cannot be converted to base data for unit %s and family %s',
                    $data,
                    $unit,
                    $family
                ),
                $e->getCode(),
                $e
            );
        }

        return $convertedData;
    }

    /**
     * Returns the standard unit of a metric family.
     *
     * @param string $family
     *
     * @return string
     */
    protected function getBaseUnit($family)
    {
        return $this->measureManager->getStandardUnitForFamily($family);
    }
}
