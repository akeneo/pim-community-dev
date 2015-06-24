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

use Akeneo\Component\FileStorage\Model\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Product asset reference
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Reference implements ReferenceInterface
{
    /** @var int */
    protected $id;

    /** @var AssetInterface */
    protected $asset;

    /** @var LocaleInterface */
    protected $locale;

    /** @var FileInterface */
    protected $file;

    /** @var ArrayCollection of VariationInterface */
    protected $variations;

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
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * {@inheritdoc}
     */
    public function setAsset(AssetInterface $asset)
    {
        $this->asset = $asset;
        $asset->addReference($this);

        return $this;
    }

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
    public function getFile()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function setFile(FileInterface $file = null)
    {
        $this->file = $file;

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
    public function addVariation(VariationInterface $variation)
    {
        if (!$this->variations->contains($variation)) {
            $this->variations->add($variation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeVariation(VariationInterface $variation)
    {
        if ($this->variations->contains($variation)) {
            $this->variations->removeElement($variation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariation(ChannelInterface $channel)
    {
        if ($this->getVariations()->isEmpty()) {
            return null;
        }

        foreach ($this->getVariations() as $variation) {
            if ($variation->getChannel() === $channel) {
                return $variation;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariation(ChannelInterface $channel)
    {
        return null !== $this->getVariation($channel);
    }
}
