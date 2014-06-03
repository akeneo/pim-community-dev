<?php

namespace Pim\Bundle\CatalogBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Abstract price backend type entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractProductPrice
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
     * @return AbstractProductPrice
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
     * @return AbstractProductPrice
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
     * @return AbstractProductPrice
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
     * @return AbstractProductPrice
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
