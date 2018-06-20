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
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;

/**
 * Creates a new asset from an uploaded file.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class BuildAsset
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var FilesUpdaterInterface */
    protected $filesUpdater;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param UploadCheckerInterface    $uploadChecker
     * @param AssetFactory              $assetFactory
     * @param AssetRepositoryInterface  $assetRepository
     * @param FilesUpdaterInterface     $filesUpdater
     * @param FileStorerInterface       $fileStorer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        AssetFactory $assetFactory,
        AssetRepositoryInterface $assetRepository,
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
     * @throws \Akeneo\Component\FileStorage\Exception\FileRemovalException
     * @throws \Akeneo\Component\FileStorage\Exception\FileTransferException
     * @throws \PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException
     *
     * @return AssetInterface
     */
    public function fromFile(\SplFileInfo $file): AssetInterface
    {
        $parsedFilename = $this->uploadChecker->getParsedFilename($file->getFilename());
        $this->uploadChecker->validateFilenameFormat($parsedFilename);

        $isLocalized = null !== $parsedFilename->getLocaleCode();
        $locale = $isLocalized ?
            $this->localeRepository->findOneBy(['code' => $parsedFilename->getLocaleCode()]) :
            null;

        $assetCode = $this->computeAssetCode($parsedFilename->getAssetCode());

        $asset = $this->assetFactory->create();
        $asset->setCode($assetCode);
        $this->assetFactory->createReferences($asset, $isLocalized);

        $storedFile = $this->fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true);

        $reference = $asset->getReference($locale);

        if (null !== $reference) {
            $reference->setFileInfo($storedFile);
            $this->filesUpdater->resetAllVariationsFiles($reference, true);
        }

        $this->filesUpdater->updateAssetFiles($asset);

        return $asset;
    }

    /**
     * +     * @param string $assetCode
     * +     * @return string
     * +     */
    private function computeAssetCode(string $assetCode): string
    {
        $asset = $this->assetRepository->findOneByIdentifier($assetCode);

        if ($asset instanceof AssetInterface) {
            $codes = $this->assetRepository->findSimilarCodes($assetCode);

            //Necessary because findSimilarCodes can return integers, and we want to perform a strict comparison
            array_walk($codes, function (&$item) {
                $item = (string)$item;
            });

            if (!empty($codes)) {
                $nextId = 1;
                while (in_array($assetCode.'_'.$nextId, $codes)) {
                    $nextId++;
                }

                $assetCode = sprintf('%s_%d', $assetCode, $nextId);
            }
        }

        return $assetCode;
    }
}
