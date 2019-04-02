<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component;

use Akeneo\Asset\Component\Builder\MetadataBuilderRegistry;
use Akeneo\Asset\Component\Exception\MissingAssetTransformationForChannelException;
use Akeneo\Asset\Component\Model\FileMetadataInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\Repository\ChannelConfigurationRepositoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileTransformer\FileTransformerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generate the variation files, store them in the filesystem and link them to the reference:
 *      - download the raw source file (reference file or user variation file) from STORAGE to /tmp
 *      - generate the variation file
 *      - extract the metadata from the variation file
 *      - store the variation file in STORAGE
 *      - set the variation file to the variation and save the variation to the database
 *      - remove generated files (variation & source) from /tmp.
 *
 * Where STORAGE is the virtual filesystem where files are stored.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class VariationFileGenerator implements VariationFileGeneratorInterface
{
    /** @var ChannelConfigurationRepositoryInterface */
    protected $configurationRepository;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var SaverInterface */
    protected $metadataSaver;

    /** @var SaverInterface */
    protected $variationSaver;

    /** @var FileTransformerInterface */
    protected $fileTransformer;

    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var MetadataBuilderRegistry */
    protected $metadaBuilderRegistry;

    /** @var string */
    protected $filesystemAlias;

    /**
     * @param ChannelConfigurationRepositoryInterface $configurationRepository
     * @param FilesystemProvider                      $filesystemProvider
     * @param SaverInterface                          $metadataSaver
     * @param SaverInterface                          $variationSaver
     * @param FileTransformerInterface                $fileTransformer
     * @param FileStorerInterface                     $fileStorer
     * @param FileFetcherInterface                    $fileFetcher
     * @param MetadataBuilderRegistry                 $metadaBuilderRegistry
     * @param string                                  $filesystemAlias
     */
    public function __construct(
        ChannelConfigurationRepositoryInterface $configurationRepository,
        FilesystemProvider $filesystemProvider,
        SaverInterface $metadataSaver, // TODO: @pull-up - To remove in master
        SaverInterface $variationSaver, // TODO: @pull-up - To remove in master
        FileTransformerInterface $fileTransformer,
        FileStorerInterface $fileStorer,
        FileFetcherInterface $fileFetcher,
        MetadataBuilderRegistry $metadaBuilderRegistry,
        $filesystemAlias
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->fileTransformer = $fileTransformer;
        $this->filesystemProvider = $filesystemProvider;
        $this->metadataSaver = $metadataSaver;
        $this->variationSaver = $variationSaver;
        $this->fileStorer = $fileStorer;
        $this->fileFetcher = $fileFetcher;
        $this->metadaBuilderRegistry = $metadaBuilderRegistry;
        $this->filesystemAlias = $filesystemAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(VariationInterface $variation): void
    {
        $locale = $variation->getReference()->getLocale();
        $channel = $variation->getChannel();
        $rawTransformations = $this->retrieveChannelTransformationsConfiguration($channel);
        $sourceFile = $this->retrieveSourceFileInfo($variation);
        $outputFilename = $this->buildVariationOutputFilename($sourceFile, $channel, $locale);

        $storageFilesystem = $this->filesystemProvider->getFilesystem($this->filesystemAlias);
        $sourceFileInfo = $this->fileFetcher->fetch($storageFilesystem, $sourceFile->getKey());
        $variationFileInfo = $this->fileTransformer->transform(
            $sourceFileInfo,
            $rawTransformations,
            $outputFilename
        );

        if (null === $variationFileInfo) {
            $this->deleteFile($sourceFileInfo);

            return;
        }

        $variationMetadata = $this->extractMetadata($variationFileInfo);
        $variationFile = $this->fileStorer->store($variationFileInfo, $this->filesystemAlias, true);
        $variationMetadata->setFileInfo($variationFile);
        $variation->setFileInfo($variationFile);

        $this->deleteFile($sourceFileInfo);
    }

    /**
     * @param ChannelInterface $channel
     *
     * @throws MissingAssetTransformationForChannelException
     *
     * @return array
     */
    protected function retrieveChannelTransformationsConfiguration(ChannelInterface $channel)
    {
        $channelConfiguration = $this->configurationRepository->findOneBy(['channel' => $channel->getId()]);
        if (null === $channelConfiguration) {
            throw new MissingAssetTransformationForChannelException($channel->getCode());
        }

        return $channelConfiguration->getConfiguration();
    }

    /**
     * Retrieve the source file info of the variation and checks it's really present on the STORAGE virtual filesystem.
     *
     * @param VariationInterface $variation
     *
     * @throws \LogicException
     *
     * @return FileInfoInterface
     */
    protected function retrieveSourceFileInfo(VariationInterface $variation)
    {
        $sourceFileInfo = $variation->getSourceFileInfo();
        if (null === $sourceFileInfo) {
            $sourceFileInfo = $variation->getReference()->getFileInfo();
        }
        if (null === $sourceFileInfo) {
            throw new \LogicException(sprintf('The variation "%s" has no source file.', $variation->getId()));
        }

        $storageFilesystem = $this->filesystemProvider->getFilesystem($this->filesystemAlias);

        if (!$storageFilesystem->has($sourceFileInfo->getKey())) {
            throw new \LogicException(
                sprintf(
                    'The source file "%s" is not present on the filesystem "%s".',
                    $sourceFileInfo->getKey(),
                    $this->filesystemAlias
                )
            );
        }

        return $sourceFileInfo;
    }

    /**
     * With a file called this_is_my_source_file.txt, it will return
     *      this_is_my_source_file-en_US-ecommerce.txt or
     *      this_is_my_source_file-ecommerce.txt.
     *
     * @param FileInfoInterface $sourceFileInfo
     * @param ChannelInterface  $channel
     * @param LocaleInterface   $locale
     *
     * @return string
     */
    protected function buildVariationOutputFilename(
        FileInfoInterface $sourceFileInfo,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $extensionPattern = sprintf('/\.%s$/', $sourceFileInfo->getExtension());
        $outputFileName = preg_replace($extensionPattern, '', $sourceFileInfo->getOriginalFilename());

        if (null !== $locale) {
            $outputFileName = sprintf('%s-%s', $outputFileName, $locale->getCode());
        }

        return sprintf('%s-%s.%s', $outputFileName, $channel->getCode(), $sourceFileInfo->getExtension());
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return FileMetadataInterface
     */
    protected function extractMetadata(\SplFileInfo $file)
    {
        $metadataBuilder = $this->metadaBuilderRegistry->getByFile($file);

        return $metadataBuilder->build($file);
    }

    /**
     * @param \SplFileInfo $file
     */
    protected function deleteFile(\SplFileInfo $file): void
    {
        $fs = new Filesystem();
        $fs->remove($file->getPathname());
    }
}
