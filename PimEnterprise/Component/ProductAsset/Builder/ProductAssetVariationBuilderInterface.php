<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Builds variations related to an asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductAssetVariationBuilderInterface
{
    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetVariationInterface[]
     */
    public function buildAll(ProductAssetInterface $asset);

    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetVariationInterface[]
     */
    public function buildMissing(ProductAssetInterface $asset);

    /**
     * @param ProductAssetInterface $asset
     * @param ChannelInterface      $channel
     * @param LocaleInterface       $locale
     *
     * @return ProductAssetVariationInterface
     */
    public function buildOne(ProductAssetInterface $asset, ChannelInterface $channel, LocaleInterface $locale);
}
