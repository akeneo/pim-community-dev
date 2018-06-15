<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AddImportedReferenceFIleToAsset
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetRepository;

    /** @var FilesUpdaterInterface */
    protected $filesUpdater;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param UploadCheckerInterface                $uploadChecker
     * @param AssetFactory                          $assetFactory
     * @param IdentifiableObjectRepositoryInterface $assetRepository
     * @param FilesUpdaterInterface                 $filesUpdater
     * @param FileStorerInterface                   $fileStorer
     * @param LocaleRepositoryInterface             $localeRepository
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        AssetFactory $assetFactory,
        IdentifiableObjectRepositoryInterface $assetRepository,
        FilesUpdaterInterface $filesUpdater,
        FileStorerInterface $fileStorer,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->uploadChecker = $uploadChecker;
        $this->assetFactory = $assetFactory;
        $this->assetRepository = $assetRepository;
        $this->filesUpdater = $filesUpdater;
        $this->fileStorer = $fileStorer;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Create or update asset reference from an uploaded file
     *
     * @param \SplFileInfo $file
     *
     * @throws \Akeneo\Component\FileStorage\Exception\FileRemovalException
     * @throws \Akeneo\Component\FileStorage\Exception\FileTransferException
     * @throws \PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException
     *
     * @return AssetInterface
     */
    public function addFile(\SplFileInfo $file): AssetInterface
    {
        $parsedFilename = $this->uploadChecker->getParsedFilename($file->getFilename());
        $this->uploadChecker->validateFilenameFormat($parsedFilename);

        $isLocalized = null !== $parsedFilename->getLocaleCode();
        $locale = $isLocalized ?
            $this->localeRepository->findOneBy(['code' => $parsedFilename->getLocaleCode()]) :
            null;

        $asset = $this->assetRepository->findOneByIdentifier($parsedFilename->getAssetCode());

        if (null === $asset) {
            $asset = $this->assetFactory->create();
            $asset->setCode($parsedFilename->getAssetCode());
            $this->assetFactory->createReferences($asset, $isLocalized);
        }

        $file = $this->fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true);

        $reference = $asset->getReference($locale);

        if (null !== $reference) {
            $reference->setFileInfo($file);
            $this->filesUpdater->resetAllVariationsFiles($reference, true);
        }

        $this->filesUpdater->updateAssetFiles($asset);

        return $asset;
    }
}
