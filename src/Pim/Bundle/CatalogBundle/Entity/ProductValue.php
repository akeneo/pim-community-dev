<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\Media;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_product_value", indexes={
 *     @ORM\Index(name="value_idx", columns={"attribute_id", "locale_code", "scope_code"}),
 *     @ORM\Index(name="varchar_idx", columns={"value_string"}),
 *     @ORM\Index(name="integer_idx", columns={"value_integer"})
 * })
 * @ORM\Entity
 *
 * @ExclusionPolicy("all")
 */
class ProductValue extends AbstractEntityFlexibleValue implements ProductValueInterface
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\ProductAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $attribute;

    /**
     * @var ProductInterface $entity
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\ProductInterface", inversedBy="values")
     */
    protected $entity;

    /**
     * Store varchar value
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     */
    protected $varchar;

    /**
     * Store int value
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", precision=14, scale=4, nullable=true)
     */
    protected $decimal;

    /**
     * Store boolean value
     * @var boolean $boolean
     *
     * @ORM\Column(name="value_boolean", type="boolean", nullable=true)
     */
    protected $boolean;

    /**
     * Store text value
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     */
    protected $date;

    /**
     * Store datetime value
     * @var date $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     */
    protected $datetime;

    /**
     * Store options values
     *
     * @var ArrayCollection options
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\CatalogBundle\Entity\AttributeOption", cascade={"refresh"})
     * @ORM\JoinTable(name="pim_catalog_value_option",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;

    /**
     * Store simple option value
     *
     * @var Pim\Bundle\CatalogBundle\Entity\AttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\AttributeOption", cascade={"persist", "refresh"})
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $option;

    /**
     * Store upload values
     *
     * @var Media $media
     *
     * @ORM\OneToOne(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\Media",
     *     cascade={"persist", "refresh"},
     *     inversedBy="value"
     * )
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $media;

    /**
     * Store metric value
     *
     * @var Metric $metric
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Metric", cascade={"persist", "refresh"})
     * @ORM\JoinColumn(name="metric_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $metric;

    /**
     * Store prices value
     *
     * @var ArrayCollection $prices
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\CatalogBundle\Entity\ProductPrice",
     *     mappedBy="value",
     *     cascade={"persist", "remove", "refresh"}
     * )
     * @ORM\OrderBy({"currency" = "ASC"})
     */
    protected $prices;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->prices = new ArrayCollection();
    }

    /**
     * Remove an option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return ProductValue
     */
    public function removeOption(AbstractEntityAttributeOption $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get media
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param Pim\Bundle\CatalogBundle\Entity\Media $media
     *
     * @return ProductValue
     */
    public function setMedia(Media $media)
    {
        $media->setValue($this);
        $this->media = $media;

        return $this;
    }

    /**
     * Get metric
     *
     * @return Metric
     */
    public function getMetric()
    {
        return $this->metric;
    }

    /**
     * Set metric
     *
     * @param Metric $metric
     *
     * @return ProductValue
     */
    public function setMetric($metric)
    {
        $this->metric = $metric;

        return $this;
    }

    /**
     * Get prices
     *
     * @return array
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Get the price matching the given currency
     *
     * @param string $currency
     *
     * @return boolean|Price
     */
    public function getPrice($currency)
    {
        return $this->prices->filter(
            function ($price) use ($currency) {
                return $currency === $price->getCurrency();
            }
        )->first();
    }

    /**
     * Set prices, used for multi select to retrieve many options
     *
     * @param ArrayCollection $prices
     *
     * @return ProductValue
     */
    public function setPrices($prices)
    {
        if (null === $prices) {
            $prices = array();
        }
        $this->prices = $prices;

        return $this;
    }

    /**
     * Add price
     *
     * @param ProductPrice $price
     *
     * @return ProductValue
     */
    public function addPrice(ProductPrice $price)
    {
        $this->prices[] = $price;
        $price->setValue($this);

        return $this;
    }

    /**
     * Remove price
     *
     * @param ProductPrice $price
     *
     * @return ProductValue
     */
    public function removePrice(ProductPrice $price)
    {
        $this->prices->removeElement($price);

        return $this;
    }

    /**
     * Add missing prices
     *
     * @param array $activeCurrencies the active currency codes
     *
     * @return ProductValue
     */
    public function addMissingPrices($activeCurrencies)
    {
        $existingCurrencies = array();
        foreach ($this->getPrices() as $price) {
            $existingCurrencies[] = $price->getCurrency();
        }
        $newCurrencies = array_diff($activeCurrencies, $existingCurrencies);
        foreach ($newCurrencies as $currency) {
            $price = new ProductPrice();
            $price->setCurrency($currency);
            $this->addPrice($price);
        }

        return $this;
    }

    /**
     * Remove disabled prices
     *
     * @param array $activeCurrencies the active currency codes
     *
     * @return ProductValue
     */
    public function removeDisabledPrices($activeCurrencies)
    {
        foreach ($this->getPrices() as $price) {
            if (!in_array($price->getCurrency(), $activeCurrencies)) {
                $this->removePrice($price);
            }
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRemovable()
    {
        if (null === $this->entity) {
            return true;
        }

        return $this->entity->isAttributeRemovable($this->attribute);
    }

    /**
     * @return Product
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
