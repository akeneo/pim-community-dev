<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Price backend type entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_product_value_price")
 * @ORM\Entity
 *
 * @ExclusionPolicy("all")
 */
class ProductPrice
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
     * @var ProductValueInterface
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\ProductValueInterface", inversedBy="prices")
     * @ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $value;

    /**
     * Store decimal value
     * @var double $decimal
     *
     * @ORM\Column(name="data", type="decimal", scale=2, nullable=true)
     */
    protected $data;

    /**
     * Currency code
     * @var string $currency
     *
     * @ORM\Column(name="currency_code", type="string", length=5, nullable=false)
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
        return ($this->data !== null) ? $this->data.' '.$this->currency : '';
    }
}
