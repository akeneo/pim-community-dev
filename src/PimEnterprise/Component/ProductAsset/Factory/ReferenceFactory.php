<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Factory;

use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

/**
 * Reference factory
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ReferenceFactory
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var VariationFactory */
    protected $variationFactory;

    /** @var string */
    protected $referenceClass;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param VariationFactory           $variationFactory
     * @param string                     $referenceClass
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        VariationFactory $variationFactory,
        $referenceClass
    ) {
        $this->channelRepository = $channelRepository;
        $this->variationFactory  = $variationFactory;
        $this->referenceClass    = $referenceClass;
    }

    /**
     * Creates a Reference with its Variation and the given locale
     *
     * @param LocaleInterface|null $locale
     *
     * @return ReferenceInterface
     */
    public function create(LocaleInterface $locale = null)
    {
        $reference = new $this->referenceClass();
        if (null !== $locale) {
            $reference->setLocale($locale);
            $channels = $locale->getChannels();
        } else {
            $channels = $this->channelRepository->getFullChannels();
        }

        foreach ($channels as $channel) {
            $variation = $this->variationFactory->create($channel);
            $variation->setReference($reference);
        }

        return $reference;
    }
}
