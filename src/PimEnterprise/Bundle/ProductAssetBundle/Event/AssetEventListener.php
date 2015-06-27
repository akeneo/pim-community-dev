<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use PimEnterprise\Bundle\ProductAssetBundle\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;

/**
 * Asset events listenener
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetEventListener
{
    /** @var AssetFinderInterface */
    protected $assetFinder;

    /** @var VariationsCollectionFilesGeneratorInterface */
    protected $variationsCollectionFilesGenerator;

    /**
     * @param AssetFinderInterface                        $assetFinder
     * @param VariationsCollectionFilesGeneratorInterface $variationsFilesGenerator
     */
    public function __construct(
        AssetFinderInterface $assetFinder,
        VariationsCollectionFilesGeneratorInterface $variationsFilesGenerator
    ) {
        $this->assetFinder                        = $assetFinder;
        $this->variationsCollectionFilesGenerator = $variationsFilesGenerator;
    }

    /**
     * Generate missing variations for one asset or for all assets
     * Triggered by AssetEvent::FILES_UPLOAD_POST
     *
     * @param AssetEvent $event
     *
     * @return AssetEvent
     */
    public function onAssetFilesUploaded(AssetEvent $event)
    {
        $asset = $event->getSubject();

        $assetCode = null !== $asset ? $asset->getCode() : null;

        $missingVariations = $this->assetFinder->retrieveVariationsNotGenerated($assetCode);

        $this->variationsCollectionFilesGenerator->generate($missingVariations, true);

        return $event;
    }
}
