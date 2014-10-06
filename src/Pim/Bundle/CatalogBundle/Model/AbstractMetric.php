<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Abstract metric (backend type entity)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMetric implements MetricInterface
{
    /** @var int|string */
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
    public function setData($data)
    {
        $this->data = $data;

        return $this;
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
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
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
    public function setBaseData($baseData)
    {
        $this->baseData = $baseData;

        return $this;
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
    public function setBaseUnit($baseUnit)
    {
        $this->baseUnit = $baseUnit;

        return $this;
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
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return ($this->data !== null) ? sprintf('%.4F %s', $this->data, $this->unit) : '';
    }
}
