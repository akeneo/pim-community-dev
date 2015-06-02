<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

/**
 * Builds variations related to an asset reference
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductAssetVariationBuilderInterface
{
    /**
     * @param ProductAssetReferenceInterface $reference
     *
     * @return ProductAssetVariationInterface[]
     */
    public function buildAll(ProductAssetReferenceInterface $reference);

    /**
     * @param ProductAssetReferenceInterface $reference
     *
     * @return ProductAssetVariationInterface[]
     */
    public function buildMissing(ProductAssetReferenceInterface $reference);

    /**
     * @param ProductAssetReferenceInterface $reference
     * @param ChannelInterface               $channel
     *
     * @throws \LogicException in case it's impossible to build the variation
     *
     * @return ProductAssetVariationInterface
     */
    public function buildOne(ProductAssetReferenceInterface $reference, ChannelInterface $channel);
}
