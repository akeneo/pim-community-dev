<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Pim\Bundle\ProductBundle\Model\ProductValueInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product_value")
 * @ORM\Entity
 * @Oro\Loggable
 */
class ProductValue extends AbstractEntityFlexibleValue implements ProductValueInterface
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var ProductInterface $entity
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Model\ProductInterface", inversedBy="values")
     */
    protected $entity;

    /**
     * Store varchar value
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $varchar;

    /**
     * Store int value
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     * @Oro\Versioned
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", nullable=true)
     * @Oro\Versioned
     */
    protected $decimal;

    /**
     * Store boolean value
     * @var boolean $boolean
     *
     * @ORM\Column(name="value_boolean", type="boolean", nullable=true)
     * @Oro\Versioned
     */
    protected $boolean;

    /**
     * Store text value
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     * @Oro\Versioned
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     * @Oro\Versioned
     */
    protected $date;

    /**
     * Store datetime value
     * @var date $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     * @Oro\Versioned
     */
    protected $datetime;

    /**
     * Store options values
     *
     * @var ArrayCollection options
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ProductBundle\Entity\AttributeOption")
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
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\AttributeOption", cascade="persist")
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
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\ProductPrice",
     *     mappedBy="value",
     *     cascade={"persist", "remove"}
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
     * @return boolean
     */
    public function isRemovable()
    {
        if (null === $this->entity) {
            return true;
        }

        if ('pim_product_identifier' === $this->attribute->getAttributeType()) {
            return false;
        }

        if (null === $this->entity->getFamily()) {
            return true;
        }

        return !$this->entity->getFamily()->getAttributes()->contains($this->getAttribute());
    }

    /**
     * @return Product
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
