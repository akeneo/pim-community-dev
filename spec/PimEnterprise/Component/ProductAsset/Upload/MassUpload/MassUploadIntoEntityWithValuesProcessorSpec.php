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

namespace spec\PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetsTo;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetToEntityWithValues;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddImportedReferenceFIleToAsset;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadIntoEntityWithValuesProcessor;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\RetrieveAssetGenerationErrors;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoEntityWithValuesProcessorSpec extends ObjectBehavior
{
    function let(
        ImporterInterface $importer,
        AddImportedReferenceFIleToAsset $addImportedReferenceFIleToAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        RetrieveAssetGenerationErrors $retrieveAssetGenerationErrors,
        ObjectDetacherInterface $objectDetacher,
        AddAssetToEntityWithValues $addAssetToEntityWithValues
    ) {
        $this->beConstructedWith(
            $importer,
            $addImportedReferenceFIleToAsset,
            $assetSaver,
            $eventDispatcher,
            $retrieveAssetGenerationErrors,
            $objectDetacher,
            $addAssetToEntityWithValues
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassUploadIntoEntityWithValuesProcessor::class);
    }

    function it_mass_uploads_asset_files_for_existing_assets(
        $importer,
        $addImportedReferenceFIleToAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new AddAssetsTo(666, 'asset_collection');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $addImportedReferenceFIleToAsset->addFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $addAssetToEntityWithValues->add(666, 'asset_collection', ['asset_code'])->shouldBeCalled();

        $processedFiles = $this->process($uploadContext, $addAssetTo);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SUCCESS);
        $processedFiles->current()->getReason()->shouldReturn(UploadMessages::STATUS_UPDATED);
    }

    function it_mass_uploads_asset_files_for_new_assets(
        $importer,
        $addImportedReferenceFIleToAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new AddAssetsTo(666, 'asset_collection');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $addImportedReferenceFIleToAsset->addFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(null);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $addAssetToEntityWithValues->add(666, 'asset_collection', ['asset_code'])->shouldBeCalled();

        $processedFiles = $this->process($uploadContext, $addAssetTo);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SUCCESS);
        $processedFiles->current()->getReason()->shouldReturn(UploadMessages::STATUS_NEW);
    }

    function it_does_not_mass_upload_asset_files_if_there_are_errors_but_saves_the_asset_anyway(
        $importer,
        $addImportedReferenceFIleToAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new AddAssetsTo(666, 'asset_collection');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $addImportedReferenceFIleToAsset->addFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn(['An error']);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $addAssetToEntityWithValues->add(666, 'asset_collection', ['asset_code'])->shouldBeCalled();

        $processedFiles = $this->process($uploadContext, $addAssetTo);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SKIPPED);
        $processedFiles->current()->getReason()->shouldReturn('An error');
    }

    function it_does_not_mass_upload_asset_files_if_an_exception_is_thrown_during_asset_creation(
        $importer,
        $addImportedReferenceFIleToAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new AddAssetsTo(666, 'asset_collection');

        $exception = new \Exception('A fatal error!');

        $importer->getImportedFiles($uploadContext)->willReturn([$importedFile]);
        $addImportedReferenceFIleToAsset->addFile($importedFile)->willThrow($exception);

        $assetSaver->save(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $retrieveAssetGenerationErrors->fromEvent(Argument::any())->shouldNotBeCalled();
        $objectDetacher->detach(Argument::any())->shouldNotBeCalled();
        $addAssetToEntityWithValues->add(Argument::class)->shouldNotBeCalled();

        $processedFiles = $this->process($uploadContext, $addAssetTo);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_ERROR);
        $processedFiles->current()->getReason()->shouldReturn('A fatal error!');
        $processedFiles->current()->getException()->shouldReturn($exception);
    }
}
