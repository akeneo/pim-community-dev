<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Metric interface (backend type entity)
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MetricInterface
{
    /**
     * Gets the metric base unit (standard unit of the measure family).
     *
     * @return string
     */
    public function getBaseUnit();

    /**
     * Gets the metric base data (amount converted according to the
     * standard unit of the measure family).
     *
     * @return float
     */
    public function getBaseData();

    /**
     * Gets used unit.
     *
     * @return string
     */
    public function getUnit();

    /**
     * Gets the metric amount.
     *
     * @return float
     */
    public function getData();

    /**
     * Gets the measure family of the metric.
     *
     * @return string
     */
    public function getFamily();

    /**
     * Checks if the metric is equal to another one.
     *
     * @param MetricInterface $metric
     *
     * @return bool
     */
    public function isEqual(MetricInterface $metric);

    /**
     * @return string
     */
    public function __toString();
}
