<?php

namespace spec\Akeneo\Asset\Component\Upload;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Asset\Bundle\Doctrine\Common\Saver\AssetSaver;
use Akeneo\Asset\Bundle\Event\AssetEvent;
use Akeneo\Asset\Component\Factory\AssetFactory;
use Akeneo\Asset\Component\FileStorage;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\Updater\FilesUpdaterInterface;
use Akeneo\Asset\Component\Upload\ImporterInterface;
use Akeneo\Asset\Component\Upload\ParsedFilename;
use Akeneo\Asset\Component\Upload\UploadCheckerInterface;
use Akeneo\Asset\Component\Upload\UploadContext;
use Prophecy\Argument;
use SplFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MassUploadProcessorSpec extends ObjectBehavior
{
    function let(
        UploadCheckerInterface $uploadChecker,
        ImporterInterface $importer,
        AssetFactory $assetFactory,
        AssetRepositoryInterface $assetRepository,
        AssetSaver $assetSaver,
        FilesUpdaterInterface $filesUpdater,
        FileStorerInterface $fileStorer,
        LocaleRepositoryInterface $localeRepository,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $uploadChecker,
            $importer,
            $assetFactory,
            $assetRepository,
            $assetSaver,
            $filesUpdater,
            $fileStorer,
            $localeRepository,
            $eventDispatcher,
            $translator,
            $objectDetacher
        );
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Asset\Component\Upload\MassUploadProcessor');
    }

    function it_creates_an_asset_from_a_non_localizable_file(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        ParsedFilename $parsedFilename,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository
    ) {
        $this->initializeApplyImportedUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer,
            'foobar.jpg'
        );

        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn(null);

        $uploadChecker->getParsedFilename('foobar.jpg')
            ->willReturn($parsedFilename);

        $uploadChecker->validateFilenameFormat($parsedFilename)
            ->willReturn(null);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn(null);

        $assetFactory->create()
            ->willReturn($asset);
        $assetFactory->createReferences($asset, false)->shouldBeCalled();

        $asset->setCode('foobar')
            ->shouldBeCalled();

        $asset->getCode()
            ->willReturn('foobar');

        $localeRepository->findOneBy(Argument::any())
            ->shouldNotBeCalled();

        $this->applyImportedUpload($file)
            ->shouldReturn($asset);
    }

    function it_creates_an_asset_from_a_localizable_file(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        ParsedFilename $parsedFilename,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository
    ) {
        $this->initializeApplyImportedUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer,
            'foobar.jpg'
        );

        $file->getFilename()->willReturn('foobar-en_US.jpg');

        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('en_US');

        $uploadChecker->getParsedFilename('foobar-en_US.jpg')
            ->willReturn($parsedFilename);

        $uploadChecker->validateFilenameFormat($parsedFilename)
            ->willReturn(null);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn(null);

        $assetFactory->create()
            ->willReturn($asset);
        $assetFactory->createReferences($asset, true)->shouldBeCalled();

        $asset->setCode('foobar')
            ->shouldBeCalled();

        $asset->getCode()
            ->willReturn('foobar');

        $localeRepository->findOneBy(['code' => 'en_US'])
            ->shouldBeCalled();

        $this->applyImportedUpload($file)
            ->shouldReturn($asset);
    }

    function it_updates_an_asset_from_a_localizable_file(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        ParsedFilename $parsedFilename,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository
    ) {
        $this->initializeApplyImportedUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer,
            'foobar.jpg'
        );

        $file->getFilename()->willReturn('foobar-en_US.jpg');

        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('en_US');

        $uploadChecker->getParsedFilename('foobar-en_US.jpg')
            ->willReturn($parsedFilename);

        $uploadChecker->validateFilenameFormat($parsedFilename)
            ->willReturn(null);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn($asset);

        $assetFactory->create()
            ->shouldNotBeCalled();

        $asset->getCode()
            ->willReturn('foobar');

        $localeRepository->findOneBy(['code' => 'en_US'])
            ->shouldBeCalled();

        $this->applyImportedUpload($file)
            ->shouldReturn($asset);
    }

    function it_mass_upload_file(
        AssetInterface $asset,
        FileInfoInterface $fileInfo,
        ObjectDetacherInterface $objectDetacher,
        ParsedFilename $parsedFilename,
        ProcessedItemList $processedFiles,
        ReferenceInterface $reference,
        SaverInterface $assetSaver,
        SplFileInfo $file,
        UploadContext $uploadContext,
        $importer,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $eventDispatcher
    ) {

        $this->initializeApplyImportedUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer,
            'foobar-en_US.jpg'
        );

        $importer->getImportedFiles($uploadContext)->shouldBeCalled()->willReturn([$file]);

        $file->getFilename()->willReturn('foobar-en_US.jpg');

        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('en_US');

        $uploadChecker->getParsedFilename('foobar-en_US.jpg')
            ->willReturn($parsedFilename);

        $uploadChecker->validateFilenameFormat($parsedFilename)
            ->willReturn(null);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn($asset);

        $assetFactory->create()
            ->shouldNotBeCalled();

        $asset->getCode()
            ->willReturn('foobar');


        $this->applyImportedUpload($file)
            ->shouldReturn($asset);

        $asset->getId()->willReturn(null);

        $uploadChecker->getParsedFilename('foobar-en_US.jpg')
            ->willReturn($parsedFilename);

        $filesUpdater->resetAllVariationsFiles(Argument::any(), true)->shouldBeCalled();
        $assetSaver->save($asset)->shouldBeCalled();

        $event = new AssetEvent();
        $event->setProcessedList($processedFiles->getWrappedObject());
        $eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, Argument::any())
            ->willReturn($event);

        $processedFiles->getItemsInState(Argument::any())->willReturn([]);

        $objectDetacher->detach($asset)->shouldBeCalled();

        $this->applyMassUpload($uploadContext)->shouldHaveType(ProcessedItemList::class);
    }

    protected function initializeApplyImportedUpload(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $filesUpdater,
        $fileStorer,
        $filename
    ) {
        $file->getFilename()->willReturn($filename);

        $fileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true)
            ->willReturn($fileInfo);

        $asset->getReference(Argument::any())
            ->willReturn($reference);

        $reference->setFileInfo($fileInfo)
            ->shouldBeCalled();

        $filesUpdater->updateAssetFiles($asset)
            ->shouldBeCalled();
    }
}
