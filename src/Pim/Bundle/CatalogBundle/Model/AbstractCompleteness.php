<?php

namespace Pim\Bundle\CatalogBundle\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Abstract product completeness entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractCompleteness implements CompletenessInterface
{
    /** @var int|string */
    protected $id;

    /** @var LocaleInterface */
    protected $locale;

    /** @var ChannelInterface */
    protected $channel;

    /** @var int */
    protected $ratio = 100;

    /** @var int */
    protected $missingCount = 0;

    /** @var int */
    protected $requiredCount = 0;

    /** @var ProductInterface */
    protected $product;

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(LocaleInterface $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * {@inheritdoc}
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMissingCount()
    {
        return $this->missingCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setMissingCount($missingCount)
    {
        $this->missingCount = $missingCount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredCount()
    {
        return $this->requiredCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequiredCount($requiredCount)
    {
        $this->requiredCount = $requiredCount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }
}
