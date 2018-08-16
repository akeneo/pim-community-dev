<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;

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
     * This method creates a metric instance, after calculating base amount and
     * base unit accordingly the the provided measure family.
     * All the data (amount, base amount, unit, base unit, measure family) are
     * directly set during metric instantiation.
     *
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
     * Creates a new metric.
     *
     * Ideally, we should throw a PIM business exception when the MeasureBundle
     * throws one, but then the PEF would fail to save the product. So we simply
     * create a invalid metric, which will be rejected by validation, allowing
     * a proper error message to be display on the PEF.
     *
     * @param string $family
     * @param string $unit
     * @param double $data
     *
     * @return MetricInterface
     */
    public function createMetric($family, $unit, $data)
    {
        try {
            $baseData = null !== $data ?
                $this->measureConverter->setFamily($family)->convertBaseToStandard($unit, $data) :
                null;
        } catch (MeasureException $e) {
            return new $this->metricClass($family, $unit, $data, null, null);
        }

        $baseUnit = $this->getBaseUnit($family);

        $metric = new $this->metricClass($family, $unit, $data, $baseUnit, $baseData);

        return $metric;
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
