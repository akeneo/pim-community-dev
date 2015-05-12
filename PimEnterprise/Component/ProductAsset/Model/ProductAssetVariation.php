<?php

namespace PimEnterprise\Component\ProductAsset\Model;

use DamEnterprise\Component\Asset\Model\FileInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

class ProductAssetVariation implements ProductAssetVariationInterface
{
    /** @var int */
    protected $id;

    /** @var ProductAssetInterface */
    protected $asset;

    /** @var ChannelInterface */
    protected $channel;

    /** @var LocaleInterface */
    protected $locale;

    /** @var FileInterface */
    protected $file;

    public function getId()
    {
        return $this->id;
    }

    public function getAsset()
    {
        return $this->asset;
    }

    public function setAsset(ProductAssetInterface $asset)
    {
        $this->asset = $asset;

        return $this;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale(LocaleInterface $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(FileInterface $file)
    {
        $this->file = $file;

        return $this;
    }
}
