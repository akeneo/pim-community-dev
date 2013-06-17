<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product_value")
 * @ORM\Entity
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class ProductValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="ProductAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var Product $entity
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     */
    protected $entity;

    /**
     * Store options values
     *
     * @var ArrayCollection options
     *
     * @ORM\ManyToMany(targetEntity="AttributeOption")
     * @ORM\JoinTable(name="pim_product_value_option",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;

    /**
     * Store simple option value
     *
     * @var Pim\Bundle\ProductBundle\Entity\AttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="AttributeOption", cascade="persist")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $option;

    /**
     * Store upload values
     *
     * @var Media $media
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Media", cascade="persist")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $media;

    /**
     * Store metric value
     *
     * @var Metric $metric
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Metric", cascade="persist")
     * @ORM\JoinColumn(name="metric_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $metric;

    /**
     * Store prices value
     *
     * @var ArrayCollection $prices
     *
     * @ORM\OneToMany(targetEntity="ProductPrice", mappedBy="value", cascade={"persist", "remove"})
     * @ORM\OrderBy({"currency" = "ASC"})
     */
    protected $prices;

    /**
     * Remove an option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductValue
     */
    public function removeOption(AbstractEntityAttributeOption $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get media
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param \Oro\Bundle\FlexibleEntityBundle\Entity\Media $media
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\ProductValue
     */
    public function setMedia($media)
    {
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
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\ProductValue
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
     * Set prices, used for multi select to retrieve many options
     *
     * @param ArrayCollection $prices
     *
     * @return ProductValue
     */
    public function setPrices($prices)
    {
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
            $existingCurrencies[]= $price->getCurrency();
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
     * Sort price, default currency is first
     *
     * @param string $defaultCurrencyCode
     *
     * @return ProductValue
     */
    public function sortPrices($defaultCurrencyCode)
    {
        // get default price by currency
        $defaultPrice = $this->getPrices()->filter(
            function ($price) use ($defaultCurrencyCode) {
                return ($price->getCurrency() === $defaultCurrencyCode);
            }
        );
        $defaultPrice = $defaultPrice->first();
        // sort prices
        $prices = $this->getPrices();
        $sortedPrices = new ArrayCollection();
        $sortedPrices[]= $defaultPrice;
        foreach ($prices as $price) {
            if ($price->getCurrency() !== $defaultCurrencyCode) {
                $sortedPrices[]= $price;
            }
        }
        $this->setPrices($sortedPrices);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRemovable()
    {
        if (null === $this->entity || null === $this->entity->getProductFamily()) {
            return true;
        }

        return !$this->entity->getProductFamily()->getAttributes()->contains($this->getAttribute());
    }

    /**
     * @return Product
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
