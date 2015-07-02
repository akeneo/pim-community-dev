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

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;

/**
 * Finder for assets and asset related entities
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface AssetFinderInterface
{
    /**
     * @param AssetRepositoryInterface     $assetRepository
     * @param VariationRepositoryInterface $variationsRepository
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        VariationRepositoryInterface $variationsRepository
    );

    /**
     * @param string $assetCode
     *
     * @throws \LogicException
     *
     * @return AssetInterface
     */
    public function retrieveAsset($assetCode);

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
     * @param int|null $assetCode
     *
     * @return VariationInterface[]
     */
    public function retrieveVariationsNotGenerated($assetCode = null);

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
     * Retrieve all products linked to an asset
     *
     * @param AssetInterface $asset
     *
     * @return ProductInterface[]
     */
    public function retrieveAssetProducts(AssetInterface $asset);
}
