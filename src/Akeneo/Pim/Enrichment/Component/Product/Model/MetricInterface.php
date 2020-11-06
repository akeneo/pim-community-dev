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
     */
    public function getBaseUnit(): string;

    /**
     * Gets the metric base data (amount converted according to the
     * standard unit of the measure family).
     */
    public function getBaseData(): float;

    /**
     * Gets used unit.
     */
    public function getUnit(): string;

    /**
     * Gets the metric amount.
     */
    public function getData(): float;

    /**
     * Gets the measure family of the metric.
     */
    public function getFamily(): string;

    /**
     * Checks if the metric is equal to another one.
     *
     * @param MetricInterface $metric
     */
    public function isEqual(MetricInterface $metric): bool;

    /**
     * @return string
     */
    public function __toString();
}
