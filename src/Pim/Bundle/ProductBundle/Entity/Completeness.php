<?php

namespace Pim\Bundle\ProductBundle\Entity;

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
     * @var string $locale
     *
     * @ORM\Column(type="string", length=8)
     */
    protected $locale;

    /**
     * @var \Pim\Bundle\ProductBundle\Entity\Channel $channel
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Channel")
     */
    protected $channel;

    /**
     * @var float $ratio
     *
     * @ORM\Column(type="decimal", scale=2)
     */
    protected $ratio;

    /**
     * @var integer $missingCount
     *
     * @ORM\Column(name="missing_count", type="integer")
     */
    protected $missingCount;

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
     * Getter locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Setter locale
     *
     * @param string $locale
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness
     */
    public function setLocale($locale)
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
     * @return float
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * Setter ratio
     *
     * @param float $ratio
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

    public function __toString()
    {
        return $this->locale .' - '. $this->channel->getCode();
    }
}
