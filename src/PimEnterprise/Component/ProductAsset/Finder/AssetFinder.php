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

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;

/**
 * Finder for assets
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetFinder implements AssetFinderInterface
{
    /** @var VariationRepositoryInterface */
    protected $variationsRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(VariationRepositoryInterface $variationsRepository)
    {
        $this->variationsRepository = $variationsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveReference(AssetInterface $asset, LocaleInterface $locale = null)
    {
        if (null === $reference = $asset->getReference($locale)) {
            if (null === $locale) {
                $msg = sprintf('The asset "%s" has no reference without locale.', $asset->getCode());
            } else {
                $msg = sprintf(
                    'The asset "%s" has no reference for the locale "%s".',
                    $asset->getCode(),
                    $locale->getCode()
                );
            }

            throw new \LogicException($msg);
        }

        return $reference;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveVariationsNotGenerated(AssetInterface $asset = null)
    {
        $missingVariations = [];

        if (null !== $asset) {
            foreach ($asset->getVariations() as $variation) {
                if (null === $variation->getFileInfo() && null !== $variation->getSourceFileInfo()) {
                    $missingVariations[] = $variation;
                }
            }
        } else {
            $missingVariations = $this->variationsRepository->findNotGenerated();
        }

        return $missingVariations;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveVariation(ReferenceInterface $reference, ChannelInterface $channel)
    {
        if (null === $variation = $reference->getVariation($channel)) {
            throw new \LogicException(
                sprintf(
                    'The reference "%s" has no variation for the channel "%s".',
                    $reference->getId(),
                    $channel->getCode()
                )
            );
        }

        return $variation;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveVariationsNotGeneratedForAReference(ReferenceInterface $reference): array
    {
        $missingVariations = [];

        foreach ($reference->getVariations() as $variation) {
            if (null === $variation->getFileInfo() && null !== $variation->getSourceFileInfo()) {
                $missingVariations[] = $variation;
            }
        }

        return $missingVariations;
    }
}
