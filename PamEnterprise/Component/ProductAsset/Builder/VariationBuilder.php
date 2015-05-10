<?php

namespace PamEnterprise\Component\ProductAsset\Builder;

use PamEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PamEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

/**
 * Builds variations related to an asset
 */
class VariationBuilder
{
    /** @var string */
    protected $variationClass;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        $variationClass = 'PamEnterprise\Component\ProductAsset\Model\ProductAssetVariation'
    ) {
        $this->channelRepository = $channelRepository;
        $this->variationClass    = $variationClass;
    }

    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetVariationInterface[]
     */
    public function buildAll(ProductAssetInterface $asset)
    {
        $variations = [];
        $channels   = $this->channelRepository->getFullChannels();

        foreach ($channels as $channel) {
            //todo: check if locale is activated ?
            foreach ($channel->getLocales() as $locale) {
                $variations[] = $this->buildOne($asset, $channel, $locale);
            }
        }

        return $variations;
    }

    /**
     * @param ProductAssetInterface $asset
     *
     * @return ProductAssetVariationInterface[]
     */
    public function buildMissing(ProductAssetInterface $asset)
    {
        $variations = [];
        $channels   = $this->channelRepository->getFullChannels();

        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                //todo: check if locale is activated ?
                if (!$asset->hasVariation($channel, $locale)) {
                    $variations[] = $this->buildOne($asset, $channel, $locale);
                }
            }
        }

        return $variations;
    }

    /**
     * @param ProductAssetInterface $asset
     * @param ChannelInterface      $channel
     * @param LocaleInterface       $locale
     *
     * @return ProductAssetVariationInterface
     */
    public function buildOne(ProductAssetInterface $asset, ChannelInterface $channel, LocaleInterface $locale)
    {
        $variation = new $this->variationClass();
        $variation->setAsset($asset);
        $variation->setChannel($channel);
        $variation->setLocale($locale);

        return $variation;
    }
}
