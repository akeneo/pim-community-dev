<?php

namespace PimEnterprise\Component\ProductAsset\Model;

use DamEnterprise\Component\Asset\Model\FileInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

interface ProductAssetVariationInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return ProductAssetInterface
     */
    public function getAsset();

    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetVariationInterface
     */
    public function setAsset(ProductAssetInterface $asset);

    /**
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * @param ChannelInterface $channel
     *
     * @return ProductAssetVariationInterface
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * @param LocaleInterface $locale
     *
     * @return ProductAssetVariationInterface
     */
    public function setLocale(LocaleInterface $locale);

    /**
     * @return FileInterface
     */
    public function getFile();

    /**
     * @param FileInterface $file
     *
     * @return ProductAssetVariationInterface
     */
    public function setFile(FileInterface $file);
}
