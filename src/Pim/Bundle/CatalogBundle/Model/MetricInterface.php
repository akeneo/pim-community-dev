<?php

namespace Pim\Bundle\CatalogBundle\Model;

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
     * Set id
     *
     * @param int|string $id
     *
     * @return MetricInterface
     */
    public function setId($id);

    /**
     * Get base data
     *
     * @return float
     */
    public function getBaseData();

    /**
     * Set base unit
     *
     * @param string $baseUnit
     *
     * @return MetricInterface
     */
    public function setBaseUnit($baseUnit);

    /**
     * Set used unit
     *
     * @param string $unit
     *
     * @return MetricInterface
     */
    public function setUnit($unit);

    /**
     * Set data
     *
     * @param float $data
     *
     * @return MetricInterface
     */
    public function setData($data);

    /**
     * Set family
     *
     * @param string $family
     *
     * @return MetricInterface
     */
    public function setFamily($family);

    /**
     * Get used unit
     *
     * @return string $unit
     */
    public function getUnit();

    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Get data
     *
     * @return float
     */
    public function getData();

    /**
     * Set base data
     *
     * @param float $baseData
     *
     * @return MetricInterface
     */
    public function setBaseData($baseData);

    /**
     * Get family
     *
     * @return string
     */
    public function getFamily();

    /**
     * Get the product value
     *
     * @return ProductValueInterface
     */
    public function getValue();

    /**
     * Set the product value
     *
     * @param ProductValueInterface $value
     *
     * @return MetricInterface
     */
    public function setValue(ProductValueInterface $value);

    /**
     * To string
     *
     * @return string
     */
    public function __toString();
}
