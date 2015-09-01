<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
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
        RawFileStorerInterface $rawFileStorer,
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
            $rawFileStorer,
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
        FileInterface $rawFile,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $rawFileStorer,
        $localeRepository
    ) {
        $this->initializeApplyScheduledUpload($file,
            $rawFile,
            $asset,
            $reference,
            $filesUpdater,
            $rawFileStorer
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
        FileInterface $rawFile,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $rawFileStorer,
        $localeRepository
    ) {
        $this->initializeApplyScheduledUpload($file,
            $rawFile,
            $asset,
            $reference,
            $filesUpdater,
            $rawFileStorer
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
        FileInterface $rawFile,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $uploadChecker,
        $assetFactory,
        $assetRepository,
        $filesUpdater,
        $rawFileStorer,
        $localeRepository
    ) {
        $this->initializeApplyScheduledUpload($file,
            $rawFile,
            $asset,
            $reference,
            $filesUpdater,
            $rawFileStorer
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
        FileInterface $rawFile,
        AssetInterface $asset,
        ReferenceInterface $reference,
        $filesUpdater,
        $rawFileStorer
    ) {
        $file->getFilename()->willReturn('foobar.jpg');

        $rawFileStorer->store($file, FileStorage::ASSET_STORAGE_ALIAS, true)
            ->willReturn($rawFile);

        $asset->getReference(Argument::any())
            ->willReturn($reference);

        $reference->setFile($rawFile)
            ->shouldBeCalled();

        $filesUpdater->updateAssetFiles($asset)
            ->shouldBeCalled();
    }
}
