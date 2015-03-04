<?php

namespace Acme\Bundle\AppBundle\Entity;

use Pim\Component\ReferenceData\Model\AbstractReferenceData;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Acme Moto entity (used as simple reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * The moto label is given by the properties "brand" and "model".
 */
class Moto extends AbstractReferenceData implements ReferenceDataInterface
{
    /** @var string */
    protected $brand;

    /** @var string */
    protected $model;

    /** @var int */
    protected $year;

    /**
     * Set brand
     *
     * @param string $brand
     * @return Car
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Car
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set year
     *
     * @param int $year
     * @return Car
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperties()
    {
        return ['brand', 'model'];
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'moto';
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return 0;
    }
}
