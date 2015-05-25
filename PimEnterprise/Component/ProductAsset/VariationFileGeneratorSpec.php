<?php

namespace spec\PimEnterprise\Component\ProductAsset;

use Akeneo\Component\FileTransformer\FileTransformerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileDownloaderInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\ChannelVariationsConfigurationRepositoryInterface;
use Prophecy\Argument;

class VariationFileGeneratorSpec extends ObjectBehavior
{
    const STORAGE_FS = 'storage';

    function let(
        ChannelVariationsConfigurationRepositoryInterface $channelConfigurationRepository,
        MountManager $mountManager,
        SaverInterface $fileSaver,
        SaverInterface $variationSaver,
        FileTransformerInterface $fileTransformer,
        RawFileStorerInterface $rawFileStorer,
        RawFileDownloaderInterface $rawFileDownloader,
        ChannelVariationsConfigurationInterface $channelConfiguration,
        ChannelInterface $ecommerce,
        ProductAssetReferenceInterface $reference,
        ProductAssetVariationInterface $variation,
        FileInterface $referenceFile,
        Filesystem $filesystem
    ) {
        $channelConfigurationRepository->findOneBy(Argument::any())->willReturn($channelConfiguration);

        $ecommerce->getId()->willReturn(12);
        $ecommerce->getCode()->willReturn('ecommerce');

        $reference->getId()->willReturn(45);
        $reference->getVariation($ecommerce)->willReturn($variation);
        $reference->getFile()->willReturn($referenceFile);

        $referenceFile->getPathname()->willReturn('path/to/my_original_file.txt');
        $referenceFile->getStorage()->willReturn(self::STORAGE_FS);

        $mountManager->getFilesystem(self::STORAGE_FS)->willReturn($filesystem);
        $filesystem->has('path/to/my_original_file.txt')->willReturn(true);

        $this->beConstructedWith(
            $channelConfigurationRepository,
            $mountManager,
            $fileSaver,
            $variationSaver,
            $fileTransformer,
            $rawFileStorer,
            $rawFileDownloader
        );
    }

    function it_generates_the_variation_file_from_a_reference(
        $reference,
        $referenceFile,
        $ecommerce,
        $rawFileDownloader,
        $filesystem,
        $channelConfiguration,
        $fileTransformer,
        $rawFileStorer,
        $fileSaver,
        $variation,
        $variationSaver,
        LocaleInterface $fr,
        \SplFileInfo $referenceFileInfo,
        \SplFileInfo $variationFileInfo,
        FileInterface $variationFile
    ) {
        $referencePathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        touch($referencePathname);

        $referenceFileInfo->getPathname()->willReturn($referencePathname);

        $referenceFile->getExtension()->willReturn('txt');
        $referenceFile->getOriginalFilename()->willReturn('my originial file.txt');
        $fr->getCode()->willReturn('fr_FR');

        $channelConfiguration->getConfiguration()->willReturn(['pipeline' => ['t1', 't2']]);

        $rawFileDownloader->download($referenceFile, $filesystem)->willReturn($referenceFileInfo);
        $fileTransformer->transform(
            $referenceFileInfo,
            ['t1', 't2'],
            'my originial file-fr_FR-ecommerce.txt'
        )->willReturn($variationFileInfo);
        $rawFileStorer->store($variationFileInfo, self::STORAGE_FS)->willReturn($variationFile);

        $fileSaver->save($variationFile)->shouldBeCalled();
        $variationSaver->save($variation)->shouldBeCalled();
        $variation->setFile($variationFile)->shouldBeCalled();

        $this->generateFromReference($reference, $ecommerce, $fr);
    }

    function it_generates_the_variation_file_from_an_asset(
        $reference,
        $referenceFile,
        $ecommerce,
        $rawFileDownloader,
        $filesystem,
        $channelConfiguration,
        $fileTransformer,
        $rawFileStorer,
        $fileSaver,
        $variation,
        $variationSaver,
        LocaleInterface $fr,
        ProductAssetInterface $asset,
        \SplFileInfo $referenceFileInfo,
        \SplFileInfo $variationFileInfo,
        FileInterface $variationFile
    ) {
        $referencePathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        touch($referencePathname);

        $asset->getReference($fr)->willReturn($reference);
        $referenceFileInfo->getPathname()->willReturn($referencePathname);

        $referenceFile->getExtension()->willReturn('txt');
        $referenceFile->getOriginalFilename()->willReturn('my originial file.txt');
        $fr->getCode()->willReturn('fr_FR');

        $channelConfiguration->getConfiguration()->willReturn(['pipeline' => ['t1', 't2']]);

        $rawFileDownloader->download($referenceFile, $filesystem)->willReturn($referenceFileInfo);
        $fileTransformer->transform(
            $referenceFileInfo,
            ['t1', 't2'],
            'my originial file-fr_FR-ecommerce.txt'
        )->willReturn($variationFileInfo);
        $rawFileStorer->store($variationFileInfo, self::STORAGE_FS)->willReturn($variationFile);

        $fileSaver->save($variationFile)->shouldBeCalled();
        $variationSaver->save($variation)->shouldBeCalled();
        $variation->setFile($variationFile)->shouldBeCalled();

        $this->generateFromAsset($asset, $ecommerce, $fr);
    }

    function it_throws_an_exception_if_the_asset_has_no_reference_for_a_locale(
        $ecommerce,
        ProductAssetInterface $asset,
        LocaleInterface $fr
    ) {
        $fr->getCode()->willReturn('fr_FR');
        $asset->getCode()->willReturn('asset1');
        $asset->getReference($fr)->willReturn(null);

        $this->shouldThrow(
            new \LogicException('The asset "asset1" has no reference for the locale "fr_FR".')
        )->during('generateFromAsset', [$asset, $ecommerce, $fr]);
    }

    function it_throws_an_exception_if_the_asset_has_no_unlocalized_reference(
        $ecommerce,
        ProductAssetInterface $asset
    ) {
        $asset->getCode()->willReturn('asset1');
        $asset->getReference(null)->willReturn(null);

        $this->shouldThrow(
            new \LogicException('The asset "asset1" has no reference without locale.')
        )->during('generateFromAsset', [$asset, $ecommerce]);
    }

    function it_throws_an_exception_if_the_channel_variation_configuration_can_not_be_retrieved(
        ChannelInterface $channel,
        $channelConfigurationRepository,
        ProductAssetReferenceInterface $reference
    ) {
        $channel->getId()->willReturn(12);
        $channel->getCode()->willReturn('ecommerce');

        $channelConfigurationRepository->findOneBy(Argument::any())->willReturn(null);

        $this->shouldThrow(
            new \LogicException('No variations configuration exists for the channel "ecommerce".')
        )->during('generateFromReference', [$reference, $channel]);
    }

    function it_throws_an_exception_if_there_is_no_variation(
        $ecommerce,
        ProductAssetReferenceInterface $reference
    ) {
        $reference->getId()->willReturn(45);
        $reference->getVariation($ecommerce)->willReturn(null);

        $this->shouldThrow(
            new \LogicException('The reference "45" has no variation for the channel "ecommerce".')
        )->during('generateFromReference', [$reference, $ecommerce]);
    }

    function it_throws_an_exception_if_the_reference_has_no_file($ecommerce, $reference)
    {
        $reference->getFile()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('The reference "45" has no file.')
        )->during('generateFromReference', [$reference, $ecommerce]);
    }

    function it_throws_an_exception_if_the_reference__file_is_not_on_the_filesystem(
        $ecommerce,
        $reference,
        $mountManager,
        FileInterface $referenceFile,
        Filesystem $filesystem
    ) {
        $referenceFile->getPathname()->willReturn('path/to/file.txt');
        $referenceFile->getStorage()->willReturn(self::STORAGE_FS);

        $mountManager->getFilesystem(self::STORAGE_FS)->willReturn($filesystem);
        $filesystem->has('path/to/file.txt')->willReturn(false);

        $this->shouldThrow(
            new \LogicException('The reference file "path/to/file.txt" is not present on the filesystem.')
        )->during('generateFromReference', [$reference, $ecommerce]);
    }


}
