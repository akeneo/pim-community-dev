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
 * - reads all files uploaded from the front ends,
 * - creates the corresponding asset.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadProcessor
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
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->importer = $importer;
        $this->assetBuilder = $assetBuilder;
        $this->assetSaver = $assetSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->retrieveAssetGenerationErrors = $retrieveAssetGenerationErrors;
        $this->objectDetacher = $objectDetacher;
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
                if (isset($asset)) {
                    $this->objectDetacher->detach($asset);
                }
            }
        }

        return $processedFiles;
    }
}
