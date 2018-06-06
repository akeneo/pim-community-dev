<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;

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

    /** @var FileInfoInterface */
    protected $fileInfo;

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
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function setFileInfo(FileInfoInterface $fileInfo = null)
    {
        $this->fileInfo = $fileInfo;

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

        return $this;
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
