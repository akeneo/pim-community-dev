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

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
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

        if (null !== $reference->getFile()) {
            $variation->setFile(null);
            $variation->setSourceFile($reference->getFile());
            $variation->setLocked(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteReferenceFile(ReferenceInterface $reference)
    {
        $reference->setFile(null);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteVariationFile(VariationInterface $variation)
    {
        $variation->setFile(null);
        $variation->setSourceFile(null);
        $variation->setLocked(true);
    }

    /**
     * {@inheritdoc}
     */
    public function resetAllUploadedFiles(AssetInterface $asset)
    {
        foreach ($asset->getReferences() as $reference) {
            $referenceFile = $reference->getFile();
            if (null !== $referenceFile) {
                if (null === $referenceFile->getId()) {
                    $reference->setFile(null);
                } else {
                    $referenceFile->setUploadedFile(null);
                }
            }
            foreach ($reference->getVariations() as $variation) {
                $variationFile = $variation->getFile();
                if (null !== $variationFile) {
                    if (null === $variationFile->getId()) {
                        $variation->setFile(null);
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
        if (null !== $variation->getFile() && null !== $uploadedFile = $variation->getFile()->getUploadedFile()) {
            $file = $this->rawFileStorer->store($uploadedFile, FileStorage::ASSET_STORAGE_ALIAS);
            $variation->setSourceFile($file);
            $variation->setFile($file);
            $variation->setLocked(true);
        }
        // TODO required because of sf form collections
        if (null !== $variation->getFile() && null === $variation->getFile()->getId()) {
            $variation->setFile(null);
        }
        if (null !== $variation->getSourceFile() && null === $variation->getSourceFile()->getId()) {
            $variation->setSourceFile(null);
        }
    }

    /**
     * Update a reference file with an uploaded file
     *
     * @param ReferenceInterface $reference
     */
    protected function updateReferenceFile(ReferenceInterface $reference)
    {
        if (null !== $reference->getFile() && null !== $uploadedFile = $reference->getFile()->getUploadedFile()) {
            $file = $this->rawFileStorer->store($uploadedFile, FileStorage::ASSET_STORAGE_ALIAS);
            $reference->setFile($file);
            $this->resetAllVariationsFiles($reference);
        }
        // TODO required because of sf form collections
        if (null !== $reference->getFile() && null === $reference->getFile()->getId()) {
            $reference->setFile(null);
        }
    }
}
