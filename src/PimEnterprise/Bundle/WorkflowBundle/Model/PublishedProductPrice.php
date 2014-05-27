<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;

/**
 * Published product price, business code is in AbstractProduct
 *
 * TODO : we could introduce an abstract media (as for product or product value) and inherit it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @ExclusionPolicy("all")
 */
class PublishedProductPrice
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var ProductValueInterface
     */
    protected $value;

    /**
     * Store decimal value
     * @var double $decimal
     */
    protected $data;

    /**
     * Currency code
     * @var string $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param decimal $data
     * @param string  $currency
     */
    public function __construct($data = null, $currency = null)
    {
        $this->data = $data;
        $this->currency = $currency;
    }

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
     * @return Price
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
     * @return Price
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get used currency
     *
     * @return string $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set used currency
     *
     * @param string $currency
     *
     * @return Price
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get value
     *
     * @return ProductValueInterface $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductPrice
     */
    public function setValue(ProductValueInterface $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return ($this->data !== null) ? sprintf('%.2F %s', $this->data, $this->currency) : '';
    }
}
