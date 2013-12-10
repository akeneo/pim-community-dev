<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Metric backend type entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_flexibleentity_metric")
 * @ORM\Entity
 */
class Metric
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Store decimal value
     * @var double $data
     *
     * @ORM\Column(name="data", type="decimal", precision=14, scale=4, nullable=true)
     */
    protected $data;

    /**
     * Unit code
     * @var string $unit
     *
     * @ORM\Column(name="unit_code", type="string", length=20, nullable=true)
     */
    protected $unit;

    /**
     * Base data value
     * @var double $baseData
     *
     * @ORM\Column(name="baseData", type="decimal", precision=14, scale=4, nullable=true)
     */
    protected $baseData;

    /**
     * Base unit value
     * @var string $baseUnit
     *
     * @ORM\Column(name="baseUnit", type="string", length=20, nullable=true)
     */
    protected $baseUnit;

    /**
     * Measure family
     * @var string $family
     *
     * @ORM\Column(name="family", type="string", length=20, nullable=true)
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
     * @return Metric
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
     * @return Metric
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get used unit

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
     * @return Metric
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
     * @return Metric
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
     * @return Metric
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
     * @return Metric
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
        if ($this->data === null) {
            return '';
        }

        return sprintf(
            '%s %s',
            rtrim(rtrim(sprintf('%f', $this->data), '0'), '.'),
            $this->unit
        );
    }
}
