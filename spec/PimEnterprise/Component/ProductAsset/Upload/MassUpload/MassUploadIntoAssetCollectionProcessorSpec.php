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
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\ImporterInterface;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\EntityToAddAssetsInto;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetToEntityWithValues;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AssetBuilder;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadIntoAssetCollectionProcessor;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\RetrieveAssetGenerationErrors;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoAssetCollectionProcessorSpec extends ObjectBehavior
{
    function let(
        ImporterInterface $importer,
        AssetBuilder $buildAsset,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher,
        RetrieveAssetGenerationErrors $retrieveAssetGenerationErrors,
        ObjectDetacherInterface $objectDetacher,
        AddAssetToEntityWithValues $addAssetToEntityWithValues
    ) {
        $this->beConstructedWith(
            $importer,
            $buildAsset,
            $assetSaver,
            $eventDispatcher,
            $retrieveAssetGenerationErrors,
            $objectDetacher,
            $addAssetToEntityWithValues
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassUploadIntoAssetCollectionProcessor::class);
    }

    function it_mass_uploads_asset_files_for_existing_assets(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new EntityToAddAssetsInto('foobar', 'asset_collection');

        $importer->getImportedFilesFromNames($uploadContext, ['car.png'])->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $addAssetToEntityWithValues->add('foobar', 'asset_collection', ['asset_code'])->shouldBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext, $addAssetTo, ['car.png']);

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
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new EntityToAddAssetsInto('foobar', 'asset_collection');

        $importer->getImportedFilesFromNames($uploadContext, ['car.png'])->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(null);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $addAssetToEntityWithValues->add('foobar', 'asset_collection', ['asset_code'])->shouldBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext, $addAssetTo, ['car.png']);

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
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new EntityToAddAssetsInto('foobar', 'asset_collection');

        $importer->getImportedFilesFromNames($uploadContext, ['car.png'])->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(42);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn(['An error']);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $addAssetToEntityWithValues->add('foobar', 'asset_collection', ['asset_code'])->shouldBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext, $addAssetTo, ['car.png']);

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
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new EntityToAddAssetsInto('foobar', 'asset_collection');

        $exception = new \Exception('A fatal error!');

        $importer->getImportedFilesFromNames($uploadContext, ['car.png'])->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willThrow($exception);

        $assetSaver->save(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $retrieveAssetGenerationErrors->fromEvent(Argument::any())->shouldNotBeCalled();
        $objectDetacher->detach(Argument::any())->shouldNotBeCalled();
        $addAssetToEntityWithValues->add(Argument::class)->shouldNotBeCalled();

        $processedFiles = $this->applyMassUpload($uploadContext, $addAssetTo, ['car.png']);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(1);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_ERROR);
        $processedFiles->current()->getReason()->shouldReturn('A fatal error!');
        $processedFiles->current()->getException()->shouldReturn($exception);
    }

    function it_mass_uploads_asset_files_for_new_assets_without_adding_them_to_a_product(
        $importer,
        $buildAsset,
        $assetSaver,
        $eventDispatcher,
        $retrieveAssetGenerationErrors,
        $objectDetacher,
        $addAssetToEntityWithValues,
        \SplFileInfo $importedFile,
        AssetInterface $asset
    ) {
        $uploadContext = new UploadContext('/tmp/pim/file_storage', 'username');
        $addAssetTo = new EntityToAddAssetsInto('foobar', 'asset_collection');

        $importer->getImportedFilesFromNames($uploadContext, ['car.png'])->willReturn([$importedFile]);
        $buildAsset->buildFromFile($importedFile)->willReturn($asset);
        $asset->getId()->willReturn(null);
        $asset->getCode()->willReturn('asset_code');

        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent($asset);
        $eventDispatcher
            ->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::type(AssetEvent::class))
            ->willReturn($event);
        $retrieveAssetGenerationErrors->fromEvent($event)->willReturn([]);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $exception = new \InvalidArgumentException('Invalid product');
        $addAssetToEntityWithValues->add('foobar', 'asset_collection', ['asset_code'])->willThrow($exception);

        $processedFiles = $this->applyMassUpload($uploadContext, $addAssetTo, ['car.png']);

        $processedFiles->shouldBeAnInstanceOf(ProcessedItemList::class);
        $processedFiles->count()->shouldReturn(2);
        $processedFiles->current()->getItem()->shouldReturn($importedFile);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_SUCCESS);
        $processedFiles->current()->getReason()->shouldReturn(UploadMessages::STATUS_NEW);
        $processedFiles->next();
        $processedFiles->current()->getItem()->shouldReturn($addAssetTo);
        $processedFiles->current()->getState()->shouldReturn(ProcessedItem::STATE_ERROR);
        $processedFiles->current()->getReason()->shouldReturn('Invalid product');
        $processedFiles->current()->getException()->shouldReturn($exception);
    }
}
