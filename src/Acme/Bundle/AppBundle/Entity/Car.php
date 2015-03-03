<?php

namespace Acme\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\CatalogBundle\Model\ReferenceDataInterface;

/**
 * Acme Car entity (used as multi reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Car implements ReferenceDataInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $brand;

    /** @var string */
    protected $model;

    /** @var int */
    protected $year;

    /**
     * Get id
     *
     * @return int 
     */
    public function getId()
    {
        return $this->id;
    }

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

    public function getIdentifier()
    {
        return sprintf('%s - %s', $this->brand, $this->model);
    }

    public function getIdentifierProperties()
    {
        return ['brand', 'model'];
    }

    public function getType()
    {
        return 'car';
    }

    public function getSortOrder()
    {
        return 0;
    }
}
