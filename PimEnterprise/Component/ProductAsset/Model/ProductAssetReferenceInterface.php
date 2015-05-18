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
 * Product asset reference interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductAssetReferenceInterface
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

    /**
     * @return ArrayCollection
     */
    public function getVariations();

    /**
     * @param ArrayCollection $variations
     *
     * @return ProductAssetInterface
     */
    public function setVariations(ArrayCollection $variations);

    /**
     * @param ProductAssetVariationInterface $variation
     *
     * @return ProductAssetInterface
     */
    public function addVariation(ProductAssetVariationInterface $variation);

    /**
     * @param ProductAssetVariationInterface $variation
     *
     * @return ProductAssetInterface
     */
    public function removeVariation(ProductAssetVariationInterface $variation);

    /**
     * @param ChannelInterface $channel
     *
     * @return ProductAssetVariationInterface|null
     */
    public function getVariation(ChannelInterface $channel);

    /**
     * @param ChannelInterface $channel
     *
     * @return bool
     */
    public function hasVariation(ChannelInterface $channel);
}
