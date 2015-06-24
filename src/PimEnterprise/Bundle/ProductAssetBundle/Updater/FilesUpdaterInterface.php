<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Updater;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
interface FilesUpdaterInterface
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param RawFileStorerInterface   $rawFileStorer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RawFileStorerInterface $rawFileStorer
    );

    /**
     * @param AssetInterface $asset
     */
    public function updateAssetFiles(AssetInterface $asset);

    /**
     * Delete file from reference
     *
     * @param ReferenceInterface $reference
     */
    public function deleteReferenceFile(ReferenceInterface $reference);

    /**
     * Delete file from variation
     *
     * @param VariationInterface $variation
     */
    public function deleteVariationFile(VariationInterface $variation);

    /**
     * Reset variations files with the reference
     *
     * @param ReferenceInterface $reference
     * @param bool               $force Force to process even locked variations
     */
    public function resetAllVariationsFiles(ReferenceInterface $reference, $force = false);

    /**
     * Reset variation file with its reference
     *
     * @param VariationInterface $variation
     */
    public function resetVariationFile(VariationInterface $variation);

    /**
     * Reset all asset's uploaded files
     *
     * @param AssetInterface $asset
     */
    public function resetAllUploadedFiles(AssetInterface $asset);
}
