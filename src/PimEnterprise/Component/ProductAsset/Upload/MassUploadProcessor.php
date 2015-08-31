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
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;

/**
 * Process mass uploaded files
 * For a given username :
 * - read all scheduled files
 * - create or update asset
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadProcessor
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var SchedulerInterface */
    protected $scheduler;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var BulkSaverInterface */
    protected $assetSaver;

    /** @var FilesUpdaterInterface */
    protected $filesUpdater;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param UploadCheckerInterface    $uploadChecker
     * @param SchedulerInterface        $scheduler
     * @param AssetFactory              $assetFactory
     * @param AssetRepositoryInterface  $assetRepository
     * @param BulkSaverInterface        $assetSaver
     * @param FilesUpdaterInterface     $filesUpdater
     * @param RawFileStorerInterface    $rawFileStorer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        SchedulerInterface $scheduler,
        AssetFactory $assetFactory,
        AssetRepositoryInterface $assetRepository,
        BulkSaverInterface $assetSaver,
        FilesUpdaterInterface $filesUpdater,
        RawFileStorerInterface $rawFileStorer,
        LocaleRepositoryInterface $localeRepository
    ) {
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
     * Process all scheduled uploaded files
     *
     * @param UploadContext $uploadContext
     *
     * @return ProcessedItemList
     */
    public function applyMassUpload(UploadContext $uploadContext)
    {
        $processedFiles = new ProcessedItemList();

        $scheduledFiles = $this->scheduler->getScheduledFiles($uploadContext);

        $assetsToSave = [];

        foreach ($scheduledFiles as $file) {
            try {
                $asset          = $this->applyScheduledUpload($file);
                $reason         = null === $asset->getId() ? UploadMessages::STATUS_NEW : UploadMessages::STATUS_UPDATED;
                $assetsToSave[] = $asset;
                $processedFiles->addItem($file, ProcessedItem::STATE_SUCCESS, $reason);
            } catch (\Exception $e) {
                $processedFiles->addItem($file, ProcessedItem::STATE_ERROR, $e->getMessage());
            }
        }

        $this->assetSaver->saveAll($assetsToSave, ['flush' => true, 'schedule' => true]);

        return $processedFiles;
    }

    /**
     * Create or update asset reference from an uploaded file
     *
     * @param \SplFileInfo $file
     *
     * @return AssetInterface
     */
    public function applyScheduledUpload(\SplFileInfo $file)
    {
        $assetInfo   = $this->uploadChecker->parseFilename($file->getFilename());
        $isLocalized = null !== $assetInfo['locale'];

        $asset = $this->assetRepository->findOneByIdentifier($assetInfo['code']);

        if (null === $asset) {
            $asset = $this->assetFactory->create($isLocalized);
            $asset->setCode($assetInfo['code']);
        }

        $file = $this->rawFileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true);

        $locale = $isLocalized ? $this->localeRepository->findOneBy(['code' => $assetInfo['locale']]) : null;

        $reference = $asset->getReference($locale);
        if (null !== $reference) {
            $reference->setFile($file);
        }

        $this->filesUpdater->updateAssetFiles($asset);

        return $asset;
    }
}
