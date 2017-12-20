<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Finder;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Finder for assets and asset related entities
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface AssetFinderInterface
{
    /**
     * @param AssetInterface  $asset
     * @param LocaleInterface $locale
     *
     * @throws \LogicException
     *
     * @return ReferenceInterface
     */
    public function retrieveReference(AssetInterface $asset, LocaleInterface $locale = null);

    /**
     * @param AssetInterface|null $asset
     *
     * @return VariationInterface[]
     */
    public function retrieveVariationsNotGenerated(AssetInterface $asset = null);

    /**
     * @param ReferenceInterface $reference
     * @param ChannelInterface   $channel
     *
     * @throws \LogicException
     *
     * @return VariationInterface
     */
    public function retrieveVariation(ReferenceInterface $reference, ChannelInterface $channel);

    /**
     * Retrieve all variations that are not generated for a given reference.
     *
     * @param ReferenceInterface $reference
     *
     * @return VariationInterface[]
     */
    public function retrieveVariationsNotGeneratedForAReference(ReferenceInterface $reference): array;
}
