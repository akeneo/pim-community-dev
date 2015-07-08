<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Completeness\Checker\Attribute;

use Pim\Bundle\CatalogBundle\Completeness\Checker\Attribute\AttributeCompleteCheckerInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetCollectionCompleteChecker implements AttributeCompleteCheckerInterface
{
    /** @var AssetFinderInterface */
    protected $assetFinder;

    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ProductValueInterface $value,
        ChannelInterface $channel,
        $localeCode = null
    ) {
        $assets = $value->getAssets();

        if (null === $assets) {
            return false;
        }

        foreach ($assets as $asset) {
            if (true === $this->checkByAsset($asset, $channel, $localeCode)) {
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
     * @param string|null      $localeCode
     *
     * @return bool
     */
    protected function checkByAsset(AssetInterface $asset, ChannelInterface $channel, $localeCode = null)
    {
        $variations = $asset->getVariations();

        foreach ($variations as $variation) {
            if ($variation->isComplete($localeCode, $channel->getCode())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return 'pim_assets_collection' === $attribute->getAttributeType();
    }
}
