<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetSaver;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\Upload\SchedulerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use Prophecy\Argument;
use SplFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MassUploadProcessorSpec extends ObjectBehavior
{
    function let(
        UploadCheckerInterface $uploadChecker,
        SchedulerInterface $scheduler,
        AssetFactory $assetFactory,
        AssetRepositoryInterface $assetRepository,
        AssetSaver $assetSaver,
        FilesUpdaterInterface $filesUpdater,
        FileStorerInterface $fileStorer,
        LocaleRepositoryInterface $localeRepository,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith($uploadChecker,
            $scheduler,
            $assetFactory,
            $assetRepository,
            $assetSaver,
            $filesUpdater,
            $fileStorer,
            $localeRepository,
            $eventDispatcher,
            $translator
        );
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\MassUploadProcessor');
    }

    function it_creates_an_asset_from_a_non_localizable_file(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository
    ) {
        $this->initializeApplyScheduledUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer
        );

        $assetInfos = [
            'code'   => 'foobar',
            'locale' => null
        ];

        $uploadChecker->parseFilename('foobar.jpg')
            ->willReturn($assetInfos);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn(null);

        $assetFactory->create(false)
            ->willReturn($asset);

        $asset->setCode('foobar')
            ->shouldBeCalled();

        $asset->getCode()
            ->willReturn('foobar');

        $localeRepository->findOneBy(Argument::any())
            ->shouldNotBeCalled();

        $this->applyScheduledUpload($file)
            ->shouldReturn($asset);
    }

    function it_creates_an_asset_from_a_localizable_file(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository
    ) {
        $this->initializeApplyScheduledUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer
        );

        $assetInfos = [
            'code'   => 'foobar',
            'locale' => 'en_US'
        ];

        $uploadChecker->parseFilename('foobar.jpg')
            ->willReturn($assetInfos);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn(null);

        $assetFactory->create(true)
            ->willReturn($asset);

        $asset->setCode('foobar')
            ->shouldBeCalled();

        $asset->getCode()
            ->willReturn('foobar');

        $localeRepository->findOneBy(['code' => 'en_US'])
            ->shouldBeCalled();

        $this->applyScheduledUpload($file)
            ->shouldReturn($asset);
    }

    function it_updates_an_asset_from_a_localizable_file(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $fileStorer,
        $localeRepository
    ) {
        $this->initializeApplyScheduledUpload($file,
            $fileInfo,
            $asset,
            $reference,
            $filesUpdater,
            $fileStorer
        );

        $assetInfos = [
            'code'   => 'foobar',
            'locale' => 'en_US'
        ];

        $uploadChecker->parseFilename('foobar.jpg')
            ->willReturn($assetInfos);

        $assetRepository->findOneByIdentifier('foobar')
            ->willReturn($asset);

        $assetFactory->create(Argument::any())
            ->shouldNotBeCalled();

        $asset->getCode()
            ->willReturn('foobar');

        $localeRepository->findOneBy(['code' => 'en_US'])
            ->shouldBeCalled();

        $this->applyScheduledUpload($file)
            ->shouldReturn($asset);
    }

    protected function initializeApplyScheduledUpload(
        SplFileInfo $file,
        FileInfoInterface $fileInfo,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $filesUpdater,
        $fileStorer
    ) {
        $file->getFilename()->willReturn('foobar.jpg');

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
