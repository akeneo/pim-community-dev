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

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Product asset variation interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
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
