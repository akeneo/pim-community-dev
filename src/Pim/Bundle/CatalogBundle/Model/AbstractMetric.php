<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Abstract metric (backend type entity)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMetric
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * Store decimal value
     *
     * @var double $data
     */
    protected $data;

    /**
     * Unit code
     *
     * @var string $unit
     */
    protected $unit;

    /**
     * Base data value
     *
     * @var double $baseData
     */
    protected $baseData;

    /**
     * Base unit value
     *
     * @var string $baseUnit
     */
    protected $baseUnit;

    /**
     * Measure family
     *
     * @var string $family
     */
    protected $family;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractMetric
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get data
     *
     * @return double
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param double $data
     *
     * @return AbstractMetric
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get used unit
     *
     * @return string $unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set used unit
     *
     * @param string $unit
     *
     * @return AbstractMetric
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get base data
     *
     * @return double
     */
    public function getBaseData()
    {
        return $this->baseData;
    }

    /**
     * Set base data
     *
     * @param double $baseData
     *
     * @return AbstractMetric
     */
    public function setBaseData($baseData)
    {
        $this->baseData = $baseData;

        return $this;
    }

    /**
     * Get base unit
     *
     * @return string
     */
    public function getBaseUnit()
    {
        return $this->baseUnit;
    }

    /**
     * Set base unit
     *
     * @param string $baseUnit
     *
     * @return AbstractMetric
     */
    public function setBaseUnit($baseUnit)
    {
        $this->baseUnit = $baseUnit;

        return $this;
    }

    /**
     * Get family
     *
     * @return string
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Set family
     *
     * @param string $family
     *
     * @return AbstractMetric
     */
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return ($this->data !== null) ? sprintf('%.4F %s', $this->data, $this->unit) : '';
    }
}
