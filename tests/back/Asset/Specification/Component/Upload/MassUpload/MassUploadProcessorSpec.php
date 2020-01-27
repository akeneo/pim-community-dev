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

namespace Specification\Akeneo\Asset\Component\Upload\MassUpload;

use Akeneo\Asset\Bundle\Event\AssetEvent;
use Akeneo\Asset\Component\Model\Asset;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Upload\ImporterInterface;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Asset\Component\Upload\UploadMessages;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Upload\MassUpload\AssetBuilder;
use Akeneo\Asset\Component\Upload\MassUpload\RetrieveAssetGenerationErrors;
use Akeneo\Asset\Component\Upload\MassUpload\MassUploadProcessor;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadProcessorSpec extends ObjectBehavior
{
    function let(
        ImporterInterface $importer,
        AssetBuilder $buildAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        RetrieveAssetGenerationErrors $retrieveAssetGenerationErrors,
        EntityManagerClearerInterface $entityManagerClearer
    ) {
        $this->beConstructedWith(
            $importer,
            $buildAsset,
            $assetSaver,
            $eventDispatcher,
            $retrieveAssetGenerationErrors,
            $entityManagerClearer
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassUploadProcessor::class);
    }

    function it_mass_uploads_asset_files_for_existing_assets(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $entityManagerClearer,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');

        $importer->import($uploadContext)->shouldBeCalled();

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $entityManagerClearer->clear()->shouldNotBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SUCCESS);
        $processedFiles->current()->getReason()->shouldReturn(UploadMessages::STATUS_UPDATED);
    }

    function it_mass_uploads_asset_files_for_new_assets(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $entityManagerClearer,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');

        $importer->import($uploadContext)->shouldBeCalled();

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(null);

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $entityManagerClearer->clear()->shouldNotBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SUCCESS);
        $processedFiles->current()->getReason()->shouldReturn(UploadMessages::STATUS_NEW);
    }

    function it_does_not_mass_upload_asset_files_if_there_are_errors_but_saves_the_asset_anyway(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $entityManagerClearer,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');

        $importer->import($uploadContext)->shouldBeCalled();

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn(['An error']);

        $entityManagerClearer->clear()->shouldNotBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SKIPPED);
        $processedFiles->current()->getReason()->shouldReturn('An error');
    }

    function it_does_not_mass_upload_asset_files_if_an_exception_is_thrown_during_asset_creation(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $entityManagerClearer,
        \SplFileInfo $importedFile
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');

        $importer->import($uploadContext)->shouldBeCalled();

        $exception = new \Exception('A fatal error!');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willThrow($exception);

        $assetSaver->save(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $retrieveAssetGenerationErrors->fromEvent(Argument::any())->shouldNotBeCalled();
        $entityManagerClearer->clear()->shouldNotBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_ERROR);
        $processedFiles->current()->getReason()->shouldReturn('A fatal error!');
        $processedFiles->current()->getException()->shouldReturn($exception);
    }

    function it_mass_uploads_11_asset_files_for_existing_assets(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $entityManagerClearer,
        \SplFileInfo $importedFile,
        \SplFileInfo $importedFile2,
        \SplFileInfo $importedFile3,
        \SplFileInfo $importedFile4,
        \SplFileInfo $importedFile5,
        \SplFileInfo $importedFile6,
        \SplFileInfo $importedFile7,
        \SplFileInfo $importedFile8,
        \SplFileInfo $importedFile9,
        \SplFileInfo $importedFile10,
        \SplFileInfo $importedFile11
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');

        $importer->import($uploadContext)->shouldBeCalled();

        $importer->getImportedFiles($uploadContext)->willReturn([
            $importedFile,
            $importedFile2,
            $importedFile3,
            $importedFile4,
            $importedFile5,
            $importedFile6,
            $importedFile7,
            $importedFile8,
            $importedFile9,
            $importedFile10,
            $importedFile11
        ]);
        $buildAsset->buildFromFile(Argument::type(\SplFileInfo::class))->will(function() {
            return new Asset();
        });

        $assetSaver->save(Argument::type(Asset::class))->shouldBeCalledTimes(11);

        $event = new AssetEvent();
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $entityManagerClearer->clear()->shouldBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(11);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SUCCESS);
        $processedFiles->current()->getReason()->shouldReturn(UploadMessages::STATUS_NEW);
    }
}
