<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Product asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductAsset implements ProductAssetInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $description;

    /** @var FileInterface */
    protected $reference;

    /** @var ArrayCollection of ProductAssetVariationInterface */
    protected $variations;

    /** @var bool */
    protected $isEnabled;

    /** @var \Datetime */
    protected $endOfUseAt;

    /** @var \Datetime */
    protected $createdAt;

    /** @var \Datetime */
    protected $updatedAt;

    public function __construct()
    {
        $this->variations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * {@inheritdoc}
     */
    public function setReference(FileInterface $reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * {@inheritdoc}
     */
    public function setVariations(ArrayCollection $variations)
    {
        $this->variations = $variations;
    }

    /**
     * {@inheritdoc}
     */
    public function addVariation(ProductAssetVariationInterface $variation)
    {
        if (!$this->variations->contains($variation)) {
            $this->variations->add($variation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeVariation(ProductAssetVariationInterface $variation)
    {
        if ($this->variations->contains($variation)) {
            $this->variations->removeElement($variation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariation(ChannelInterface $channel, LocaleInterface $locale)
    {
        foreach ($this->getVariations() as $variation) {
            if ($channel === $variation->getChannel() && $locale === $variation->getLocale()) {
                return $variation;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariation(ChannelInterface $channel, LocaleInterface $locale)
    {
        return null !== $this->getVariation($channel, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndOfUseAt()
    {
        return $this->endOfUseAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndOfUseAt(\Datetime $endOfUseAt)
    {
        $this->endOfUseAt = $endOfUseAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'code';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getCode();
    }
}
