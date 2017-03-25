<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Completeness\Checker;

use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetCollectionCompleteChecker implements ProductValueCompleteCheckerInterface
{
    /** @var AssetFinderInterface */
    protected $assetFinder;

    /**
     * @param ProductValueInterface $productValue
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     *
     * @return bool
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        if (!$productValue instanceof ProductValueInterface) {
            $message = sprintf(
                'Product value must implement %s, %s provided',
                ProductValueInterface::class,
                get_class($productValue)
            );
            throw new \InvalidArgumentException($message);
        }
        $assets = $productValue->getData();

        if (null === $assets) {
            return false;
        }

        foreach ($assets as $asset) {
            if (true === $this->checkAssetByLocaleAndChannel($asset, $channel, $locale)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if asset is complete for a tuple channel/locale
     *
     * @param AssetInterface       $asset
     * @param ChannelInterface     $channel
     * @param LocaleInterface|null $locale
     *
     * @return bool
     */
    protected function checkAssetByLocaleAndChannel(
        AssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $variations = $asset->getVariations();

        foreach ($variations as $variation) {
            if ($variation->isComplete($locale->getCode(), $channel->getCode())) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(
        ProductValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        return AttributeTypes::ASSETS_COLLECTION === $productValue->getAttribute()->getType();
    }
}
