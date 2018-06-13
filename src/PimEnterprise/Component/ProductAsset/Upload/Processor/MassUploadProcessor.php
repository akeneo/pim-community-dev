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

namespace PimEnterprise\Component\ProductAsset\Upload\Processor;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Process mass uploaded files
 * For a given username :
 * - read all imported files
 * - create or update asset
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadProcessor extends AbstractMassUploadProcessor
{
    /** @var ImporterInterface */
    protected $importer;

    /** @var AddImportedReferenceFIleToAsset */
    protected $addImportedReferenceFIleToAsset;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param ImporterInterface               $importer
     * @param AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset
     * @param SaverInterface                  $assetSaver
     * @param EventDispatcherInterface        $eventDispatcher
     * @param TranslatorInterface             $translator
     * @param ObjectDetacherInterface         $objectDetacher
     */
    public function __construct(
        ImporterInterface $importer,
        AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->importer = $importer;
        $this->addImportedReferenceFIleToAsset = $addImportedReferenceFIleToAsset;
        $this->assetSaver = $assetSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * Processes all imported uploaded files.
     *
     * @param UploadContext $uploadContext
     *
     * @return ProcessedItemList
     */
    public function process(UploadContext $uploadContext): ProcessedItemList
    {
        $processedFiles = new ProcessedItemList();

        $importedFiles = $this->importer->getImportedFiles($uploadContext);

        foreach ($importedFiles as $file) {
            try {
                $asset = $this->addImportedReferenceFIleToAsset->addFile($file);
                $reason = null === $asset->getId() ? UploadMessages::STATUS_NEW : UploadMessages::STATUS_UPDATED;

                $this->assetSaver->save($asset);

                $event = $this->eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, new AssetEvent($asset));
                $errors = $this->retrieveGenerationEventErrors($event);

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
