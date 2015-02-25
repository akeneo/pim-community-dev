<?php

namespace Acme\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue as PimProductValue;

class ProductValue extends PimProductValue
{
    /** @var ArrayCollection */
    protected $cars;

    /** @var Moto */
    protected $moto;

    public function __construct()
    {
        parent::__construct();
        $this->cars = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getCars()
    {
        return $this->cars;
    }

    /**
     * @param ArrayCollection $cars
     */
    public function setCars(ArrayCollection $cars)
    {
        $this->cars = $cars;
    }

    /**
     * @param Car $car
     */
    public function addCar(Car $car)
    {
        $this->cars->add($car);
    }

    /**
     * @param Car $car
     */
    public function removeCar(Car $car)
    {
        $this->cars->removeElement($car);
    }

    /**
     * @return Moto
     */
    public function getMoto()
    {
        return $this->moto;
    }

    /**
     * @param Moto $moto
     */
    public function setMoto($moto)
    {
        $this->moto = $moto;
    }
}
