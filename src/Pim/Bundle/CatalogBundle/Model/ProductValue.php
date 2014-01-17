<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Value for an attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class ProductValue extends AbstractEntityFlexibleValue implements ProductValueInterface
{
    /**
     * @var AbstractAttribute $attribute
     */
    protected $attribute;

    /**
     * @var integer
     */
    protected $attributeId;

    /**
     * @var ProductInterface $entity
     */
    protected $entity;

    /**
     * Store varchar value
     * @var string $varchar
     */
    protected $varchar;

    /**
     * Store integer value
     * @var integer $integer
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     */
    protected $decimal;

    /**
     * Store boolean value
     * @var boolean $boolean
     */
    protected $boolean;

    /**
     * Store text value
     * @var string $text
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     */
    protected $date;

    /**
     * Store datetime value
     * @var date $datetime
     */
    protected $datetime;

    /**
     * Store options values
     *
     * @var ArrayCollection options
     */
    protected $options;

    /**
     * Store simple option value
     *
     * @var Pim\Bundle\CatalogBundle\Entity\AttributeOption $option
     */
    protected $option;

    /**
     * Store upload values
     *
     * @var Media $media
     */
    protected $media;

    /**
     * Store metric value
     *
     * @var Metric $metric
     */
    protected $metric;

    /**
     * Store prices value
     *
     * @var ArrayCollection $prices
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
     * Set Attribute id
     *
     * @param integer $attributeId
     *
     * @return ProductValue
     */
    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;

        return $this;
    }

    /**
     * Get attribute id
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->attributeId;
    }

    /**
     * Set attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return ProductValue
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        if (null !== $attribute) {
            $this->attributeId = $attribute->getId();
        }
        $this->attribute = $attribute;

        return $this;
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
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param Media $media
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
        return isset($this->prices[$currency]) ? $this->prices[$currency] : null;
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
        $this->prices[$price->getCurrency()] = $price;
        $price->setValue($this);

        return $this;
    }

    /**
     * Adds a price for the given currency, or returns the existing price
     *
     * @param string $currency
     *
     * @return ProductPrice
     */
    public function addPriceForCurrency($currency)
    {
        if (!isset($this->prices[$currency])) {
            $this->addPrice(new ProductPrice(null, $currency));
        }

        return $this->prices[$currency];
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
        $this->prices->remove($price->getCurrency());

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
        array_walk($activeCurrencies, array($this, 'addPriceForCurrency'));

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
        foreach ($this->getPrices() as $currency => $price) {
            if (!in_array($currency, $activeCurrencies)) {
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
}
