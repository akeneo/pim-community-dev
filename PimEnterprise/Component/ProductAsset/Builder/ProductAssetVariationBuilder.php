<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

/**
 * Builds variations related to an asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductAssetVariationBuilder implements ProductAssetVariationBuilderInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var string */
    protected $variationClass;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param string                     $variationClass
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        $variationClass = 'PimEnterprise\Component\ProductAsset\Model\ProductAssetVariation'
    ) {
        $this->channelRepository = $channelRepository;
        $this->variationClass    = $variationClass;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function buildOne(ProductAssetInterface $asset, ChannelInterface $channel, LocaleInterface $locale)
    {
        $variation = new $this->variationClass();
        $variation->setAsset($asset);
        $variation->setChannel($channel);
        $variation->setLocale($locale);

        return $variation;
    }

    /**
     * {@inheritdoc}
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
}
