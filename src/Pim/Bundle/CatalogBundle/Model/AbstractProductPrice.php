<?php

namespace Pim\Bundle\CatalogBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Abstract price backend type entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractProductPrice implements ProductPriceInterface
{
    /** @var int|string */
    protected $id;

    /** @var ProductValueInterface */
    protected $value;

    /**
     * Store decimal value
     *
     * @var float
     */
    protected $data;

    /**
     * CurrencyInterface code
     *
     * @var string
     */
    protected $currency;

    /**
     * Constructor
     *
     * @param float  $data
     * @param string $currency
     */
    public function __construct($data = null, $currency = null)
    {
        $this->data = $data;
        $this->currency = $currency;
    }

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
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(ProductValueInterface $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return ($this->data !== null) ? sprintf('%.2F %s', $this->data, $this->currency) : '';
    }
}
