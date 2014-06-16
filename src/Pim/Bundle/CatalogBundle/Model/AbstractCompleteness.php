<?php

namespace Pim\Bundle\CatalogBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Abstract product completeness entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractCompleteness
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
     * @var Channel $channel
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
     * @var ProductInterface
     */
    protected $product;

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
     * @return AbstractCompleteness
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
     * @return AbstractCompleteness
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
     * @return AbstractCompleteness
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
     * @return AbstractCompleteness
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
     * @return AbstractCompleteness
     */
    public function setRequiredCount($requiredCount)
    {
        $this->requiredCount = $requiredCount;

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
     * @return AbstractCompleteness
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }
}
