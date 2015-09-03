<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\FileStorage\File\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class FilesUpdater implements FilesUpdaterInterface
{
    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param RawFileStorerInterface $rawFileStorer
     */
    public function __construct(RawFileStorerInterface $rawFileStorer)
    {
        $this->rawFileStorer = $rawFileStorer;
    }

    /**
     * Update all asset's files : reference and variations
     *
     * @param AssetInterface $asset
     */
    public function updateAssetFiles(AssetInterface $asset)
    {
        foreach ($asset->getReferences() as $reference) {
            foreach ($reference->getVariations() as $variation) {
                $this->updateVariationFile($variation);
            }
            $this->updateReferenceFile($reference);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetAllVariationsFiles(ReferenceInterface $reference, $force = false)
    {
        foreach ($reference->getVariations() as $variation) {
            if ($force || !$variation->isLocked()) {
                $this->resetVariationFile($variation);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetVariationFile(VariationInterface $variation)
    {
        $reference = $variation->getReference();

        if (null !== $reference->getFileInfo()) {
            $variation->setFileInfo(null);
            $variation->setSourceFileInfo($reference->getFileInfo());
            $variation->setLocked(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteReferenceFile(ReferenceInterface $reference)
    {
        $reference->setFileInfo(null);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteVariationFile(VariationInterface $variation)
    {
        $variation->setFileInfo(null);
        $variation->setSourceFileInfo(null);
        $variation->setLocked(true);
    }

    /**
     * {@inheritdoc}
     */
    public function resetAllUploadedFiles(AssetInterface $asset)
    {
        foreach ($asset->getReferences() as $reference) {
            $referenceFile = $reference->getFileInfo();
            if (null !== $referenceFile) {
                if (null === $referenceFile->getId()) {
                    $reference->setFileInfo(null);
                } else {
                    $referenceFile->setUploadedFile(null);
                }
            }
            foreach ($reference->getVariations() as $variation) {
                $variationFile = $variation->getFileInfo();
                if (null !== $variationFile) {
                    if (null === $variationFile->getId()) {
                        $variation->setFileInfo(null);
                    } else {
                        $variationFile->setUploadedFile(null);
                    }
                }
            }
        }
    }

    /**
     * Update a variation file with an uploaded file
     *
     * @param VariationInterface $variation
     */
    protected function updateVariationFile(VariationInterface $variation)
    {
        if (null !== $variation->getFileInfo() && null !== $uploadedFile = $variation->getFileInfo()->getUploadedFile()) {
            $file = $this->rawFileStorer->store($uploadedFile, FileStorage::ASSET_STORAGE_ALIAS);
            $variation->setSourceFileInfo($file);
            $variation->setFileInfo($file);
            $variation->setLocked(true);
        }
        // TODO required because of sf form collections
        if (null !== $variation->getFileInfo() && null === $variation->getFileInfo()->getId()) {
            $variation->setFileInfo(null);
        }
        if (null !== $variation->getSourceFileInfo() && null === $variation->getSourceFileInfo()->getId()) {
            $variation->setSourceFileInfo(null);
        }
    }

    /**
     * Update a reference file with an uploaded file
     *
     * @param ReferenceInterface $reference
     */
    protected function updateReferenceFile(ReferenceInterface $reference)
    {
        if (null !== $reference->getFileInfo() && null !== $uploadedFile = $reference->getFileInfo()->getUploadedFile()) {
            $file = $this->rawFileStorer->store($uploadedFile, FileStorage::ASSET_STORAGE_ALIAS);
            $reference->setFileInfo($file);
            $this->resetAllVariationsFiles($reference);
        }
        // TODO required because of sf form collections
        if (null !== $reference->getFileInfo() && null === $reference->getFileInfo()->getId()) {
            $reference->setFileInfo(null);
        }
    }
}
