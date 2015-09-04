<?php

namespace spec\PimEnterprise\Component\ProductAsset;

use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileTransformer\FileTransformerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Builder\MetadataBuilderInterface;
use PimEnterprise\Component\ProductAsset\Builder\MetadataBuilderRegistry;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use Prophecy\Argument;

class VariationFileGeneratorSpec extends ObjectBehavior
{
    const STORAGE_FS = 'my_storage';

    function let(
        ChannelConfigurationRepositoryInterface $channelConfigurationRepository,
        MountManager $mountManager,
        SaverInterface $metadataSaver,
        SaverInterface $variationSaver,
        FileTransformerInterface $fileTransformer,
        FileStorerInterface $fileStorer,
        FileFetcherInterface $fileFetcher,
        MetadataBuilderRegistry $metadataBuilderRegistry,
        ChannelVariationsConfigurationInterface $channelConfiguration,
        ChannelInterface $ecommerce,
        ReferenceInterface $reference,
        VariationInterface $variation,
        FileInfoInterface $sourceFileInfo,
        VariationInterface $variation,
        Filesystem $filesystem,
        LocaleInterface $en_US
    ) {
        $channelConfigurationRepository->findOneBy(Argument::any())->willReturn($channelConfiguration);
        $ecommerce->getId()->willReturn(12);
        $ecommerce->getCode()->willReturn('ecommerce');
        $reference->getLocale()->willReturn($en_US);
        $variation->getReference()->willReturn($reference);
        $variation->getChannel()->willReturn($ecommerce);
        $variation->getId()->willReturn(16);
        $variation->getSourceFileInfo()->willReturn($sourceFileInfo);
        $sourceFileInfo->getKey()->willReturn('path/to/my_original_file.txt');
        $sourceFileInfo->getExtension()->willReturn('txt');
        $sourceFileInfo->getOriginalFilename()->willReturn('my_original_file.txt');
        $sourceFileInfo->getStorage()->willReturn(self::STORAGE_FS);
        $mountManager->getFilesystem(self::STORAGE_FS)->willReturn($filesystem);
        $filesystem->has('path/to/my_original_file.txt')->willReturn(true);

        $this->beConstructedWith(
            $channelConfigurationRepository,
            $mountManager,
            $metadataSaver,
            $variationSaver,
            $fileTransformer,
            $fileStorer,
            $fileFetcher,
            $metadataBuilderRegistry,
            self::STORAGE_FS
        );
    }

    function it_generates_the_variation(
        $fileFetcher,
        $filesystem,
        $channelConfiguration,
        $fileTransformer,
        $fileStorer,
        $metadataSaver,
        $variation,
        $variationSaver,
        $metadataBuilderRegistry,
        \SplFileInfo $inputFileInfo,
        \SplFileInfo $variationFileInfo,
        FileInfoInterface $variationFile,
        FileMetadataInterface $fileMetadata,
        MetadataBuilderInterface $metadataBuilder
    ) {
        $referencePathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        touch($referencePathname);

        $metadataBuilderRegistry->getByFile($variationFileInfo)->willReturn($metadataBuilder);
        $inputFileInfo->getPathname()->willReturn($referencePathname);

        $channelConfiguration->getConfiguration()->willReturn(['t1', 't2']);

        $fileFetcher->fetch($filesystem, 'path/to/my_original_file.txt')->willReturn($inputFileInfo);
        $fileTransformer->transform(
            $inputFileInfo,
            ['t1', 't2'],
            'my_original_file--ecommerce.txt'
        )->willReturn($variationFileInfo);
        $metadataBuilder->build($variationFileInfo)->willReturn($fileMetadata);
        $fileStorer->store($variationFileInfo, self::STORAGE_FS)->willReturn($variationFile);

        $fileMetadata->setFileInfo($variationFile)->shouldBeCalled();
        $metadataSaver->save($fileMetadata)->shouldBeCalled();
        $variationSaver->save($variation)->shouldBeCalled();
        $variation->setFileInfo($variationFile)->shouldBeCalled();

        $this->generate($variation);
    }

    function it_throws_an_exception_if_the_channel_variation_configuration_can_not_be_retrieved(
        VariationInterface $variation,
        ChannelInterface $channel,
        $channelConfigurationRepository
    ) {
        $channel->getId()->willReturn(12);
        $channel->getCode()->willReturn('ecommerce');

        $channelConfigurationRepository->findOneBy(Argument::any())->willReturn(null);

        $this->shouldThrow(
            new \LogicException('No variations configuration exists for the channel "ecommerce".')
        )->during('generate', [$variation]);
    }

    function it_throws_an_exception_if_the_variation_has_no_source_file($variation)
    {
        $variation->getSourceFileInfo()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('The variation "16" has no source file.')
        )->during('generate', [$variation]);
    }

    function it_throws_an_exception_if_the_source_file_is_not_on_the_filesystem($variation, $filesystem)
    {
        $filesystem->has('path/to/my_original_file.txt')->willReturn(false);

        $this->shouldThrow(
            new \LogicException('The source file "path/to/my_original_file.txt" is not present on the filesystem "my_storage".')
        )->during('generate', [$variation]);
    }
}
