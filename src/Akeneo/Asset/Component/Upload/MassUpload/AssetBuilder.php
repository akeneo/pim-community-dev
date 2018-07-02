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

namespace Akeneo\Asset\Component\Upload\MassUpload;

use Akeneo\Asset\Component\Factory\AssetFactory;
use Akeneo\Asset\Component\FileStorage;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Updater\FilesUpdaterInterface;
use Akeneo\Asset\Component\Upload\UploadCheckerInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Creates a new asset from an uploaded file.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetBuilder
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
     * @param \SplFileInfo $file
     *
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileTransferException
     * @throws \Akeneo\Asset\Component\Upload\Exception\UploadException
     *
     * @return AssetInterface
     */
    public function buildFromFile(\SplFileInfo $file): AssetInterface
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

        $storedFile = $this->fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true);

        $reference = $asset->getReference($locale);

        if (null !== $reference) {
            $reference->setFileInfo($storedFile);
            $this->filesUpdater->resetAllVariationsFiles($reference, true);
        }

        $this->filesUpdater->updateAssetFiles($asset);

        return $asset;
    }
}
