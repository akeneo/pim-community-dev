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

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageHeightException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageWidthException;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
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
class MassUploadProcessor
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var ImporterInterface */
    protected $importer;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var FilesUpdaterInterface */
    protected $filesUpdater;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param UploadCheckerInterface    $uploadChecker
     * @param ImporterInterface         $importer
     * @param AssetFactory              $assetFactory
     * @param AssetRepositoryInterface  $assetRepository
     * @param SaverInterface            $assetSaver
     * @param FilesUpdaterInterface     $filesUpdater
     * @param FileStorerInterface       $fileStorer
     * @param LocaleRepositoryInterface $localeRepository
     * @param EventDispatcherInterface  $eventDispatcher
     * @param TranslatorInterface       $translator
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        ImporterInterface $importer,
        AssetFactory $assetFactory,
        AssetRepositoryInterface $assetRepository,
        SaverInterface $assetSaver,
        FilesUpdaterInterface $filesUpdater,
        FileStorerInterface $fileStorer,
        LocaleRepositoryInterface $localeRepository,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        $this->uploadChecker = $uploadChecker;
        $this->importer = $importer;
        $this->assetFactory = $assetFactory;
        $this->assetRepository = $assetRepository;
        $this->assetSaver = $assetSaver;
        $this->filesUpdater = $filesUpdater;
        $this->fileStorer = $fileStorer;
        $this->localeRepository = $localeRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
    }

    /**
     * Process all imported uploaded files
     *
     * @param UploadContext $uploadContext
     *
     * @return ProcessedItemList
     */
    public function applyMassUpload(UploadContext $uploadContext)
    {
        $processedFiles = new ProcessedItemList();

        $importedFiles = $this->importer->getImportedFiles($uploadContext);

        foreach ($importedFiles as $file) {
            try {
                $asset = $this->applyImportedUpload($file);
                $reason = null === $asset->getId() ? UploadMessages::STATUS_NEW : UploadMessages::STATUS_UPDATED;

                $parsedFilename = $this->uploadChecker->getParsedFilename($file->getFilename());
                if (null !== $parsedFilename->getLocaleCode()) {
                    $locale = $this->localeRepository->findOneBy(['code' => $parsedFilename->getLocaleCode()]);
                } else {
                    $locale = null;
                }

                $this->filesUpdater->resetAllVariationsFiles($asset->getReference($locale), true);
                $this->assetSaver->save($asset);

                $event = $this->eventDispatcher->dispatch(
                    AssetEvent::POST_UPLOAD_FILES,
                    new AssetEvent($asset)
                );

                $errors = $this->retrieveGenerationEventErrors($event);

                if (count($errors) > 0) {
                    $processedFiles->addItem($file, ProcessedItem::STATE_SKIPPED, implode(PHP_EOL, $errors));
                } else {
                    $processedFiles->addItem($file, ProcessedItem::STATE_SUCCESS, $reason);
                }
            } catch (\Exception $e) {
                $processedFiles->addItem($file, ProcessedItem::STATE_ERROR, $e->getMessage(), $e);
            }
        }

        return $processedFiles;
    }

    /**
     * Create or update asset reference from an uploaded file
     *
     * @param \SplFileInfo $file
     *
     * @return AssetInterface
     */
    public function applyImportedUpload(\SplFileInfo $file)
    {
        $parsedFilename = $this->uploadChecker->getParsedFilename($file->getFilename());
        $this->uploadChecker->validateFilenameFormat($parsedFilename);

        $isLocalized = null !== $parsedFilename->getLocaleCode();

        $asset = $this->assetRepository->findOneByIdentifier($parsedFilename->getAssetCode());

        if (null === $asset) {
            $asset = $this->assetFactory->create();
            $asset->setCode($parsedFilename->getAssetCode());
            $this->assetFactory->createReferences($asset, $isLocalized);
        }

        $file = $this->fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true);

        $locale = $isLocalized ?
            $this->localeRepository->findOneBy(['code' => $parsedFilename->getLocaleCode()]) :
            null;

        $reference = $asset->getReference($locale);

        if (null !== $reference) {
            $reference->setFileInfo($file);
        }

        $this->filesUpdater->updateAssetFiles($asset);

        return $asset;
    }

    /**
     * @param AssetEvent $event
     *
     * @return string[]
     */
    protected function retrieveGenerationEventErrors(AssetEvent $event)
    {
        $errors = [];
        $items = $event->getProcessedList();

        foreach ($items->getItemsInState(ProcessedItem::STATE_ERROR) as $item) {
            if (!$this->canVariationBeGeneratedForMimeType($item->getItem())) {
                continue;
            }

            $parameters = ['%channel%' => $item->getItem()->getChannel()->getCode()];
            switch (true) {
                case $item->getException() instanceof InvalidOptionsTransformationException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.invalid_options';
                    break;
                case $item->getException() instanceof ImageWidthException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.image_width_error';
                    break;
                case $item->getException() instanceof ImageHeightException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.image_height_error';
                    break;
                case $item->getException() instanceof GenericTransformationException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.not_applicable';
                    break;
                case $item->getException() instanceof NonRegisteredTransformationException:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.non_registered';
                    $parameters['%transformation%'] = $item->getException()->getTransformation();
                    $parameters['%mimeType%'] = $item->getException()->getMimeType();
                    break;
                default:
                    $template = 'pimee_product_asset.enrich_variation.flash.transformation.error';
                    break;
            }
            $errors[] = $this->translator->trans(
                $template,
                $parameters
            );
        }

        return $errors;
    }

    /**
     * @param mixed $item
     *
     * @return bool
     */
    protected function canVariationBeGeneratedForMimeType($item)
    {
        $supportedMimeTypes = [
            'image/jpeg',
            'image/tiff',
            'image/png',
        ];

        return $item instanceof VariationInterface
            && null !== $item->getReference()->getFileInfo()
            && in_array($item->getReference()->getFileInfo()->getMimeType(), $supportedMimeTypes);
    }
}
