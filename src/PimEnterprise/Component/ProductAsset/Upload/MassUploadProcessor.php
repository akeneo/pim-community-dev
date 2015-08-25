<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use SplFileInfo;

/**
 * Process mass uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadProcessor
{
    /** @var UploaderInterface */
    protected $uploader;

    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var SchedulerInterface */
    protected $scheduler;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var FilesUpdaterInterface */
    protected $filesUpdater;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param UploaderInterface         $uploader
     * @param UploadCheckerInterface    $uploadChecker
     * @param SchedulerInterface        $scheduler
     * @param AssetFactory              $assetFactory
     * @param AssetRepositoryInterface  $assetRepository
     * @param SaverInterface            $assetSaver
     * @param FilesUpdaterInterface     $filesUpdater
     * @param RawFileStorerInterface    $rawFileStorer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        UploaderInterface $uploader,
        UploadCheckerInterface $uploadChecker,
        SchedulerInterface $scheduler,
        AssetFactory $assetFactory,
        AssetRepositoryInterface $assetRepository,
        SaverInterface $assetSaver,
        FilesUpdaterInterface $filesUpdater,
        RawFileStorerInterface $rawFileStorer,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->uploader         = $uploader;
        $this->uploadChecker    = $uploadChecker;
        $this->scheduler        = $scheduler;
        $this->assetFactory     = $assetFactory;
        $this->assetRepository  = $assetRepository;
        $this->assetSaver       = $assetSaver;
        $this->filesUpdater     = $filesUpdater;
        $this->rawFileStorer    = $rawFileStorer;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @return UploaderInterface
     */
    public function getUploader()
    {
        return $this->uploader;
    }

    /**
     * Process all scheduled uploaded files
     *
     * @return ProcessedItemList
     */
    public function applyMassUpload()
    {
        $processedFiles = new ProcessedItemList();

        $this->scheduler->setSourceDirectory($this->uploader->getUserUploadDir());
        $this->scheduler->setScheduleDirectory($this->uploader->getUserScheduleDir());

        $scheduledFiles = $this->scheduler->getScheduledFiles();

        foreach ($scheduledFiles as $file) {
            try {
                $asset = $this->applyScheduledUpload($file);
                if (null === $asset->getId()) {
                    $reason = UploadStatus::STATUS_NEW;
                } else {
                    $reason = UploadStatus::STATUS_UPDATED;
                }
                $this->assetSaver->save($asset, ['flush' => true]);
                $processedFiles->addItem($file, ProcessedItem::STATE_SUCCESS, $reason);
            } catch (\Exception $e) {
                $processedFiles->addItem($file, ProcessedItem::STATE_ERROR, $e->getMessage());
            }
        }

        return $processedFiles;
    }

    /**
     * Create or update asset reference from an uploaded file
     *
     * @param SplFileInfo $file
     *
     * @return AssetInterface
     */
    public function applyScheduledUpload(SplFileInfo $file)
    {
        $assetInfo   = $this->uploadChecker->parseFilename($file->getFilename());
        $isLocalized = null !== $assetInfo['locale'];

        /** @var AssetInterface $asset */
        $asset = $this->assetRepository->findOneByIdentifier($assetInfo['code']);

        if (null === $asset) {
            $asset = $this->assetFactory->create($isLocalized);
            $asset->setCode($assetInfo['code']);

            $assets[$asset->getCode()] = $asset;
        }

        $file = $this->rawFileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true);

        $locale = null;
        if ($isLocalized) {
            $locale = $this->localeRepository->findOneBy(['code' => $assetInfo['locale']]);
        }

        if (null !== $reference = $asset->getReference($locale)) {
            $reference->setFile($file);
        }

        $this->filesUpdater->updateAssetFiles($asset);

        return $asset;
    }
}
