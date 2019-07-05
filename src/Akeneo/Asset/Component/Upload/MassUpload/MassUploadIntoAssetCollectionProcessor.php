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

use Akeneo\Asset\Bundle\Event\AssetEvent;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Upload\ImporterInterface;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Asset\Component\Upload\UploadMessages;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Processes mass uploaded asset files.
 *
 * For a given username, it:
 * - reads all files uploaded from the front end,
 * - creates the corresponding assets,
 * - adds them in the asset collection of a product or product model.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoAssetCollectionProcessor
{
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

    /** @var AddAssetToEntityWithValues */
    protected $addAssetToEntityWithValues;

    /**
     * @param ImporterInterface             $importer
     * @param AssetBuilder                  $assetBuilder
     * @param SaverInterface                $assetSaver
     * @param EventDispatcherInterface      $eventDispatcher
     * @param RetrieveAssetGenerationErrors $assetGenerationErrors
     * @param ObjectDetacherInterface       $objectDetacher
     * @param AddAssetToEntityWithValues    $addAssetToEntityWithValues
     */
    public function __construct(
        ImporterInterface $importer,
        AssetBuilder $assetBuilder,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        RetrieveAssetGenerationErrors $assetGenerationErrors,
        ObjectDetacherInterface $objectDetacher,
        AddAssetToEntityWithValues $addAssetToEntityWithValues
    ) {
        $this->importer = $importer;
        $this->assetBuilder = $assetBuilder;
        $this->assetSaver = $assetSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->retrieveAssetGenerationErrors = $assetGenerationErrors;
        $this->objectDetacher = $objectDetacher;
        $this->addAssetToEntityWithValues = $addAssetToEntityWithValues;
    }

    /**
     * Processes all imported uploaded files.
     *
     * @param UploadContext         $uploadContext
     * @param EntityToAddAssetsInto $addAssetsTo
     * @param array                 $importedFileNames
     *
     * @return ProcessedItemList
     */
    public function applyMassUpload(
        UploadContext $uploadContext,
        EntityToAddAssetsInto $addAssetsTo,
        array $importedFileNames = []
    ): ProcessedItemList {
        $processedItems = new ProcessedItemList();

        $importedFiles = $this->importer->getImportedFilesFromNames($uploadContext, $importedFileNames);

        $importedAssetCodes = [];
        foreach ($importedFiles as $file) {
            try {
                $asset = $this->assetBuilder->buildFromFile($file);
                $reason = null === $asset->getId() ? UploadMessages::STATUS_NEW : UploadMessages::STATUS_UPDATED;

                $this->assetSaver->save($asset);

                $event = $this->eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, new AssetEvent($asset));
                $errors = $this->retrieveAssetGenerationErrors->fromEvent($event);

                if (count($errors) > 0) {
                    $processedItems->addItem($file, ProcessedItem::STATE_SKIPPED, implode(PHP_EOL, $errors));
                } else {
                    $processedItems->addItem($file, ProcessedItem::STATE_SUCCESS, $reason);
                }
            } catch (\Exception $e) {
                $processedItems->addItem($file, ProcessedItem::STATE_ERROR, $e->getMessage(), $e);
            } finally {
                if (isset($asset)) {
                    $importedAssetCodes[] = $asset->getCode();
                    $this->objectDetacher->detach($asset);
                }
            }
        }

        if (!empty($importedAssetCodes)) {
            try {
                $this->addAssetToEntityWithValues->add(
                    $addAssetsTo->getEntityIdentifier(),
                    $addAssetsTo->getAttributeCode(),
                    $importedAssetCodes
                );
            } catch (\InvalidArgumentException $e) {
                $processedItems->addItem($addAssetsTo, ProcessedItem::STATE_ERROR, $e->getMessage(), $e);
            }
        }

        return $processedItems;
    }
}
