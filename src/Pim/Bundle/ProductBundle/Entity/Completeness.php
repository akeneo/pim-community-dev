<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Product completeness entity
 * Define the completeness of the enrichment of the product
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(name="pim_product_completeness")
 */
class Completeness
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \Pim\Bundle\ProductBundle\Entity\Locale $locale
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Locale")
     */
    protected $locale;

    /**
     * @var \Pim\Bundle\ProductBundle\Entity\Channel $channel
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Channel")
     */
    protected $channel;

    /**
     * @var integer $ratio
     *
     * @ORM\Column(type="integer")
     */
    protected $ratio = 100;

    /**
     * @var integer $missingCount
     *
     * @ORM\Column(name="missing_count", type="integer")
     */
    protected $missingCount = 0;

    /**
     * @var integer $requiredCount
     *
     * @ORM\Column(name="required_count", type="integer")
     */
    protected $requiredCount = 0;

    /**
     * @var boolean $toReindex
     *
     * @ORM\Column(name="to_reindex", type="boolean")
     */
    protected $toReindex = false;

    /**
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var \Pim\Bundle\ProductBundle\Entity\Product
     *
     * @ORM\ManyToOne(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\Product",
     *     inversedBy="completenesses"
     * )
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductAttribute")
     * @ORM\JoinTable(
     *     name="pim_product_completenesses_attributes",
     *     joinColumns={@ORM\JoinColumn(name="completeness_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")}
     * )
     */
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
     * @return \Pim\Bundle\ProductBundle\Entity\Locale
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Getter channel
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Channel
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function setRequiredCount($requiredCount)
    {
        $this->requiredCount = $requiredCount;

        return $this;
    }

    /**
     * Getter to reindex
     *
     * @return boolean
     */
    public function isToReindex()
    {
        return $this->toReindex;
    }

    /**
     * Setter to reindex
     *
     * @param boolean $toReindex
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function setToReindex($toReindex)
    {
        $this->toReindex = $toReindex;

        return $this;
    }

    /**
     * Getter updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Setter updated datetime
     *
     * @param datetime $updated
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Getter product
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Setter product
     *
     * @param Product $product
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Getter for the missing attributes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
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
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function removeMissingAttribute(ProductAttribute $attribute)
    {
        $this->missingAttributes->removeElement($attribute);

        return $this;
    }
}
