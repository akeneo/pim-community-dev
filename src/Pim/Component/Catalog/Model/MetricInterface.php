<?php

namespace Pim\Component\Catalog\Model;

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
     * Get base unit
     *
     * @return string
     */
    public function getBaseUnit();

    /**
     * Get base data
     *
     * @return float
     */
    public function getBaseData();

    /**
     * Get used unit
     *
     * @return string
     */
    public function getUnit();

    /**
     * Get data
     *
     * @return float
     */
    public function getData();

    /**
     * Get family
     *
     * @return string
     */
    public function getFamily();

    /**
     * To string
     *
     * @return string
     */
    public function __toString();
}
