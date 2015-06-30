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

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;

/**
 * Finder for assets
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetFinder implements AssetFinderInterface
{
    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var VariationRepositoryInterface */
    protected $variationsRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        VariationRepositoryInterface $variationsRepository
    ) {
        $this->assetRepository      = $assetRepository;
        $this->variationsRepository = $variationsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAsset($assetCode)
    {
        if (null === $asset = $this->assetRepository->findOneByIdentifier($assetCode)) {
            throw new \LogicException(sprintf('The asset "%s" does not exist.', $assetCode));
        }

        return $asset;
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
    public function retrieveVariationsNotGenerated($assetCode = null)
    {
        $missingVariations = [];

        if (null !== $assetCode) {
            $asset = $this->retrieveAsset($assetCode);
            foreach ($asset->getVariations() as $variation) {
                if (null === $variation->getFile() && null !== $variation->getSourceFile()) {
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
}
