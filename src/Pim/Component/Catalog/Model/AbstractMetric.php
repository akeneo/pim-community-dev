<?php

namespace Pim\Component\Catalog\Model;

/**
 * Abstract metric (backend type entity)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMetric implements MetricInterface
{
    /**
     * Store decimal value
     *
     * @var float
     */
    protected $data;

    /**
     * Unit code
     *
     * @var string
     */
    protected $unit;

    /**
     * Base data value
     *
     * @var float
     */
    protected $baseData;

    /**
     * Base unit value
     *
     * @var string
     */
    protected $baseUnit;

    /**
     * Measure family
     *
     * @var string
     */
    protected $family;

    /**
     * @param string $family
     * @param string $unit
     * @param string $data
     * @param string $baseUnit
     * @param string $baseData
     */
    public function __construct($family, $unit, $data, $baseUnit, $baseData)
    {
        $this->family = $family;
        $this->unit = $unit;
        $this->data = $data;
        $this->baseUnit = $baseUnit;
        $this->baseData = $baseData;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseData()
    {
        return $this->baseData;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUnit()
    {
        return $this->baseUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return ($this->data !== null) ? sprintf('%.4F %s', $this->data, $this->unit) : '';
    }
}
