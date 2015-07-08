<?php

/*
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    protected function checkByAsset(AssetInterface $asset, ChannelInterface $channel, $localeCode = null)
    {
        $variations = $asset->getVariations();

        foreach ($variations as $variation) {
            if ($channel->getCode() === $variation->getChannel()->getCode()
                && ($localeCode === $variation->getReference()->getLocale()
                    || null === $variation->getReference()->getLocale())
                && null !== $variation->getFile()
            ) {
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
