<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Completeness\Checker;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetCollectionCompleteChecker implements ValueCompleteCheckerInterface
{
    /**
     * @param ValueInterface        $productValue
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     *
     * @return bool
     */
    public function isComplete(
        ValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
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
     * @param AssetInterface   $asset
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return bool
     */
    protected function checkAssetByLocaleAndChannel(
        AssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale
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
        ValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        return AttributeTypes::ASSETS_COLLECTION === $productValue->getAttribute()->getType();
    }
}
