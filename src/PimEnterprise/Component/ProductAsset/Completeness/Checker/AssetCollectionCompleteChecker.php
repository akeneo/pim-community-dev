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

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use PimEnterprise\Bundle\CatalogBundle\Model\ProductValueInterface as EnterpriseProductValueInterface;
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
     * @internal param ProductValueInterface $value
     * @internal param null $localeCode
     *
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        if (!$productValue instanceof EnterpriseProductValueInterface) {
            throw new \InvalidArgumentException('Product value must implement %s');
        }
        $assets = $productValue->getAssets();

        if (null === $assets) {
            return false;
        }

        foreach ($assets as $asset) {
            if (true === $this->checkByAsset($asset, $channel, $locale)) {
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
    protected function checkByAsset(AssetInterface $asset, ChannelInterface $channel, LocaleInterface $locale = null)
    {
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
    public function supportsValue(ProductValueInterface $productValue)
    {
        return 'pim_assets_collection' === $productValue->getAttribute()->getAttributeType();
    }
}
