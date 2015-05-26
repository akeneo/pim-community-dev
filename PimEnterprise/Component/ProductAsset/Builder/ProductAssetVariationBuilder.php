<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;

/**
 * Builds variations related to an asset reference
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
    public function buildMissing(ProductAssetReferenceInterface $reference)
    {
        $variations = [];
        $channels   = $this->channelRepository->getFullChannels();

        foreach ($channels as $channel) {
            if ($this->canBuildOne($reference, $channel) && !$reference->hasVariation($channel)) {
                $variations[] = $this->buildOne($reference, $channel);
            }
        }

        return $variations;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOne(ProductAssetReferenceInterface $reference, ChannelInterface $channel)
    {
        if (!$this->canBuildOne($reference, $channel)) {
            throw new \LogicException(
                sprintf(
                    'Impossible to build a variation on channel "%s" for the reference with locale "%s".',
                    $channel->getCode(),
                    $reference->getLocale()->getCode()
                )
            );
        }

        $variation = new $this->variationClass();
        $variation->setReference($reference);
        $variation->setChannel($channel);

        return $variation;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAll(ProductAssetReferenceInterface $reference)
    {
        $variations = [];
        $channels   = $this->channelRepository->getFullChannels();

        foreach ($channels as $channel) {
            if ($this->canBuildOne($reference, $channel)) {
                $variations[] = $this->buildOne($reference, $channel);
            }
        }

        return $variations;
    }

    /**
     * Possible to build a variation on a reference for a channel when:
     *    - either the reference has no locale
     *    - either the reference has a locale, this locale is activated and belongs to the channel
     *
     * @param ProductAssetReferenceInterface $reference
     * @param ChannelInterface               $channel
     *
     * @return bool
     */
    protected function canBuildOne(ProductAssetReferenceInterface $reference, ChannelInterface $channel)
    {
        $referenceLocale = $reference->getLocale();

        if (null === $referenceLocale) {
            return true;
        }

        if ($channel->hasLocale($referenceLocale) && $referenceLocale->isActivated()) {
            return true;
        }

        return false;
    }
}
