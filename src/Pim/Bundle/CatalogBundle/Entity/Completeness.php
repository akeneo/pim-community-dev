<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product completeness entity
 * Define the completeness of the enrichment of the product
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class Completeness
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var Locale $locale
     */
    protected $locale;

    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\Channel $channel
     */
    protected $channel;

    /**
     * @var integer $ratio
     */
    protected $ratio = 100;

    /**
     * @var integer $missingCount
     */
    protected $missingCount = 0;

    /**
     * @var integer $requiredCount
     */
    protected $requiredCount = 0;

    /**
     * @var int|string
     */
    protected $productId;

    /**
     * @var \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    protected $product;

    protected $missingAttributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->missingAttributes = new ArrayCollection();
    }

    /**
     * Getter locale
     *
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Setter locale
     *
     * @param Locale $locale
     *
     * @return Completeness
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Getter channel
     *
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Setter channel
     *
     * @param Channel $channel
     *
     * @return Completeness
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Getter ratio
     *
     * @return integer
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * Setter ratio
     *
     * @param integer $ratio
     *
     * @return Completeness
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * Getter missing count
     *
     * @return integer
     */
    public function getMissingCount()
    {
        return $this->missingCount;
    }

    /**
     * Setter missing count
     *
     * @param integer $missingCount
     *
     * @return Completeness
     */
    public function setMissingCount($missingCount)
    {
        $this->missingCount = $missingCount;

        return $this;
    }

    /**
     * Getter required count
     *
     * @return integer
     */
    public function getRequiredCount()
    {
        return $this->requiredCount;
    }

    /**
     * Setter required count
     *
     * @param integer $requiredCount
     *
     * @return Completeness
     */
    public function setRequiredCount($requiredCount)
    {
        $this->requiredCount = $requiredCount;

        return $this;
    }

    /**
     * Get the associated product id
     *
     * @return int|string productId
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set the current product id
     *
     * @param int|string product id
     *
     * @return Completeness
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Getter product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Setter product
     *
     * @param ProductInterface $product
     *
     * @return Completeness
     */
    public function setProduct(ProductInterface $product)
    {
        $this->productId = $product->getId();
        $this->product = $product;

        return $this;
    }

    /**
     * Getter for the missing attributes
     *
     * @return ArrayCollection
     */
    public function getMissingAttributes()
    {
        return $this->missingAttributes;
    }

    /**
     * Setter for the missing attributes
     *
     * @param array $missingAttributes
     *
     * @return Completeness
     */
    public function setMissingAttributes(array $missingAttributes = array())
    {
        $this->missingAttributes = new ArrayCollection($missingAttributes);

        return $this;
    }

    /**
     * Add attribute to the missing attributes collection
     *
     * @param ProductAttribute $attribute
     *
     * @return Completeness
     */
    public function addMissingAttribute(ProductAttribute $attribute)
    {
        if (!$this->missingAttributes->contains($attribute)) {
            $this->missingAttributes->add($attribute);
        }

        return $this;
    }

    /**
     * Remove attribute from the missing attributes collection
     *
     * @param ProductAttribute $attribute
     *
     * @return Completeness
     */
    public function removeMissingAttribute(ProductAttribute $attribute)
    {
        $this->missingAttributes->removeElement($attribute);

        return $this;
    }
}
