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

namespace PimEnterprise\Component\ProductAsset\Upload\Processor;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoEntityWithValuesProcessor extends AbstractMassUploadProcessor
{
    /** @var ImporterInterface */
    protected $importer;

    /** @var AddImportedReferenceFIleToAsset */
    protected $addImportedReferenceFIleToAsset;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var ObjectRepository */
    protected $entityWithValueRepository;

    /** @var ObjectUpdaterInterface */
    protected $entityWithValueUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $entityWithValueSaver;

    /**
     * @param ImporterInterface               $importer
     * @param AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset
     * @param SaverInterface                  $assetSaver
     * @param EventDispatcherInterface        $eventDispatcher
     * @param TranslatorInterface             $translator
     * @param ObjectDetacherInterface         $objectDetacher
     * @param ObjectRepository                $entityWithValueRepository
     * @param ObjectUpdaterInterface          $entityWithValueUpdater
     * @param ValidatorInterface              $validator
     * @param SaverInterface                  $entityWithValueSaver
     */
    public function __construct(
        ImporterInterface $importer,
        AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectDetacherInterface $objectDetacher,
        ObjectRepository $entityWithValueRepository,
        ObjectUpdaterInterface $entityWithValueUpdater,
        ValidatorInterface $validator,
        SaverInterface $entityWithValueSaver
    ) {
        $this->importer = $importer;
        $this->addImportedReferenceFIleToAsset = $addImportedReferenceFIleToAsset;
        $this->assetSaver = $assetSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->objectDetacher = $objectDetacher;
        $this->entityWithValueRepository = $entityWithValueRepository;
        $this->entityWithValueUpdater = $entityWithValueUpdater;
        $this->validator = $validator;
        $this->entityWithValueSaver = $entityWithValueSaver;
    }

    /**
     * Processes all imported uploaded files.
     *
     * @param UploadContext $uploadContext
     * @param AddAssetsTo   $addAssetsTo
     *
     * @return ProcessedItemList
     */
    public function process(UploadContext $uploadContext, AddAssetsTo $addAssetsTo): ProcessedItemList
    {
        $processedFiles = new ProcessedItemList();

        $importedFiles = $this->importer->getImportedFiles($uploadContext);

        $importedAssetCodes = [];
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
                    $importedAssetCodes[] = $asset->getCode();
                    $this->objectDetacher->detach($asset);
                }
            }
        }

        $this->addImportedAssetsToEntityWithValues(
            $addAssetsTo->getEntityId(),
            $addAssetsTo->getAttributeCode(),
            $importedAssetCodes
        );

        return $processedFiles;
    }

    /**
     * @param int    $entityId
     * @param string $attributeCode
     * @param array  $importedAssetCodes
     */
    protected function addImportedAssetsToEntityWithValues(
        int $entityId,
        string $attributeCode,
        array $importedAssetCodes
    ): void {
        if (empty($importedAssetCodes)) {
            return;
        }

        $entityWithValues = $this->entityWithValueRepository->find($entityId);
        $previousValue = $entityWithValues->getValue($attributeCode);

        $previousAssetCodes = [];
        if (null !== $previousValue) {
            $previousAssetCodes = array_map(function (AssetInterface $asset) {
                return $asset->getCode();
            }, $previousValue->getData());
        }

        $this->entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                $attributeCode => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => array_merge($previousAssetCodes, $importedAssetCodes),
                ]],
            ],
        ]);

        $errors = $this->validator->validate($entityWithValues);
        if (0 < $errors->count()) {
            return; // TODO Do something with those errors…
        }

        $this->entityWithValueSaver->save($entityWithValues);
    }
}
