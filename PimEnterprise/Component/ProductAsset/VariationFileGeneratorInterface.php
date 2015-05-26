<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;

/**
 * Variation file generator interface.
 *
 * Generate the variation files, store them in the filesystem and link them to the reference.
 *
 * TODO: maybe we'll need some generateMissing() functions
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface VariationFileGeneratorInterface
{
    /**
     * Generate the variation files from an asset.
     *
     * @param ProductAssetInterface $asset
     * @param ChannelInterface      $channel
     * @param LocaleInterface       $locale
     *
     * @throws \LogicException
     */
    public function generateFromAsset(
        ProductAssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    );

    /**
     * Generate the variation files from a reference.
     *
     * @param ProductAssetReferenceInterface $reference
     * @param ChannelInterface               $channel
     * @param LocaleInterface                $locale
     *
     * @throws \LogicException
     */
    public function generateFromReference(
        ProductAssetReferenceInterface $reference,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    );
}
