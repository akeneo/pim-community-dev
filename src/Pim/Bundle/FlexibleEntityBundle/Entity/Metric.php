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
     * @var double $decimal
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
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->data.' '.$this->unit;
    }
}
