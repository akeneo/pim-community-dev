<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Processes mass uploaded asset files.
 *
 * For a given username, it:
 * - reads all files uploaded from the front ends,
 * - creates the corresponding asset.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadProcessor
{
    private const BATCH_SIZE =  10;

    /** @var ImporterInterface */
    protected $importer;

    /** @var AssetBuilder */
    protected $assetBuilder;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var RetrieveAssetGenerationErrors */
    protected $retrieveAssetGenerationErrors;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    /**
     * @param ImporterInterface             $importer
     * @param AssetBuilder                  $assetBuilder
     * @param SaverInterface                $assetSaver
     * @param EventDispatcherInterface      $eventDispatcher
     * @param RetrieveAssetGenerationErrors $retrieveAssetGenerationErrors
     * @param ObjectDetacherInterface       $objectDetacher
     */
    public function __construct(
        ImporterInterface $importer,
        AssetBuilder $assetBuilder,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        RetrieveAssetGenerationErrors $retrieveAssetGenerationErrors,
        ObjectDetacherInterface $objectDetacher, // TODO: to remove in master
        EntityManagerClearerInterface $entityManagerClearer = null // TODO: remove null in master
    ) {
        $this->importer = $importer;
        $this->assetBuilder = $assetBuilder;
        $this->assetSaver = $assetSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->retrieveAssetGenerationErrors = $retrieveAssetGenerationErrors;
        $this->objectDetacher = $objectDetacher;
        $this->entityManagerClearer = $entityManagerClearer;
    }

    /**
     * Processes all imported uploaded files.
     *
     * @param UploadContext $uploadContext
     *
     * @return ProcessedItemList
     */
    public function applyMassUpload(UploadContext $uploadContext): ProcessedItemList
    {
        $processedFiles = new ProcessedItemList();

        $this->importer->import($uploadContext);

        $importedFiles = $this->importer->getImportedFiles($uploadContext);
        $i = 0;

        foreach ($importedFiles as $file) {
            try {
                $asset = $this->assetBuilder->buildFromFile($file);
                $reason = null === $asset->getId() ? UploadMessages::STATUS_NEW : UploadMessages::STATUS_UPDATED;

                $this->assetSaver->save($asset);

                $event = $this->eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, new AssetEvent($asset));
                $errors = $this->retrieveAssetGenerationErrors->fromEvent($event);

                if (count($errors) > 0) {
                    $processedFiles->addItem($file, ProcessedItem::STATE_SKIPPED, implode(PHP_EOL, $errors));
                } else {
                    $processedFiles->addItem($file, ProcessedItem::STATE_SUCCESS, $reason);
                }
            } catch (\Exception $e) {
                $processedFiles->addItem($file, ProcessedItem::STATE_ERROR, $e->getMessage(), $e);
            } finally {
                $i++;
                if (isset($asset)) {
                    if (null !== $this->entityManagerClearer) {
                        if ($i % self::BATCH_SIZE === 0) {
                            $this->entityManagerClearer->clear();
                        }
                    } else { // TODO: to remove in master
                        $this->objectDetacher->detach($asset);
                    }
                }
            }
        }

        return $processedFiles;
    }
}
