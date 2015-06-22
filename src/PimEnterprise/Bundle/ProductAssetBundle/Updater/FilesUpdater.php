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
use PimEnterprise\Component\ProductAsset\ProductAssetFileSystems;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FilesUpdater implements FilesUpdaterInterface
{
    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param RawFileStorerInterface   $rawFileStorer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->rawFileStorer   = $rawFileStorer;
    }

    /**
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
     * @param VariationInterface $variation
     */
    protected function updateVariationFile(VariationInterface $variation)
    {
        if (null !== $variation->getFile() && null !== $uploadedFile = $variation->getFile()->getUploadedFile()) {
            $file = $this->rawFileStorer->store($uploadedFile, ProductAssetFileSystems::FS_STORAGE);
            $variation->setSourceFile($file);
            $variation->setFile(null);
            $variation->setLocked(true);
        }
        if (null !== $variation->getFile() && null === $variation->getFile()->getId()) {
            $variation->setFile(null);
        }
        if (null !== $variation->getSourceFile() && null === $variation->getSourceFile()->getId()) {
            $variation->setSourceFile(null);
        }
    }

    /**
     * @param ReferenceInterface $reference
     */
    protected function updateReferenceFile(ReferenceInterface $reference)
    {
        if (null !== $uploadedFile = $reference->getFile()->getUploadedFile()) {
            $file = $this->rawFileStorer->store($uploadedFile, ProductAssetFileSystems::FS_STORAGE);
            $reference->setFile($file);
            $this->resetVariationsFiles($reference);
        }
        if (null !== $reference->getFile() && null === $reference->getFile()->getId()) {
            $reference->setFile(null);
        }
    }

    /**
     * @param ReferenceInterface $reference
     */
    protected function resetVariationsFiles(ReferenceInterface $reference)
    {
        foreach ($reference->getVariations() as $variation) {
            if (!$variation->isLocked()) {
                $variation->setFile(null);
                $variation->setSourceFile($reference->getFile());
            }
        }
    }
}
