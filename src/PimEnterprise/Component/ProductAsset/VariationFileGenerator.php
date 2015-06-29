<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Akeneo\Component\FileTransformer\FileTransformerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Builder\MetadataBuilderRegistry;
use PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generate the variation files, store them in the filesystem and link them to the reference:
 *      - download the raw source file (reference file or user variation file) from STORAGE to /tmp
 *      - generate the variation file
 *      - extract the metadata from the variation file
 *      - store the variation file in STORAGE
 *      - set the variation file to the variation and save the variation to the database
 *
 * Where STORAGE is the virtual filesystem where files are stored.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class VariationFileGenerator implements VariationFileGeneratorInterface
{
    /** @var ChannelConfigurationRepositoryInterface */
    protected $configurationRepository;

    /** @var MountManager */
    protected $mountManager;

    /** @var SaverInterface */
    protected $metadataSaver;

    /** @var SaverInterface */
    protected $variationSaver;

    /** @var FileTransformerInterface */
    protected $fileTransformer;

    /** @var RawFileFetcherInterface */
    protected $rawFileFetcher;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var MetadataBuilderRegistry */
    protected $metadaBuilderRegistry;

    /** @var string */
    protected $filesystemAlias;

    /**
     * @param ChannelConfigurationRepositoryInterface $configurationRepository
     * @param MountManager                            $mountManager
     * @param SaverInterface                          $metadataSaver
     * @param SaverInterface                          $variationSaver
     * @param FileTransformerInterface                $fileTransformer
     * @param RawFileStorerInterface                  $rawFileStorer
     * @param RawFileFetcherInterface                 $rawFileFetcher
     * @param MetadataBuilderRegistry                 $metadaBuilderRegistry
     * @param string                                  $filesystemAlias
     */
    public function __construct(
        ChannelConfigurationRepositoryInterface $configurationRepository,
        MountManager $mountManager,
        SaverInterface $metadataSaver,
        SaverInterface $variationSaver,
        FileTransformerInterface $fileTransformer,
        RawFileStorerInterface $rawFileStorer,
        RawFileFetcherInterface $rawFileFetcher,
        MetadataBuilderRegistry $metadaBuilderRegistry,
        $filesystemAlias
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->fileTransformer         = $fileTransformer;
        $this->mountManager            = $mountManager;
        $this->metadataSaver           = $metadataSaver;
        $this->variationSaver          = $variationSaver;
        $this->rawFileStorer           = $rawFileStorer;
        $this->rawFileFetcher          = $rawFileFetcher;
        $this->metadaBuilderRegistry   = $metadaBuilderRegistry;
        $this->filesystemAlias         = $filesystemAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(VariationInterface $variation)
    {
        $locale             = $variation->getReference()->getLocale();
        $channel            = $variation->getChannel();
        $rawTransformations = $this->retrieveChannelTransformationsConfiguration($channel);
        $sourceFile         = $this->retrieveSourceFile($variation);
        $outputFilename     = $this->buildVariationOutputFilename($sourceFile, $channel, $locale);

        $storageFilesystem  = $this->mountManager->getFilesystem($this->filesystemAlias);
        $sourceFileInfo     = $this->rawFileFetcher->fetch($sourceFile, $storageFilesystem);
        $variationFileInfo  = $this->fileTransformer->transform(
            $sourceFileInfo,
            $rawTransformations,
            $outputFilename
        );
        $variationMetadata = $this->extractMetadata($variationFileInfo);
        $variationFile     = $this->rawFileStorer->store($variationFileInfo, $this->filesystemAlias);

        $variationMetadata->setFile($variationFile);
        $this->metadataSaver->save($variationMetadata, ['flush_only_object' => true]);

        $variation->setFile($variationFile);
        $this->variationSaver->save($variation, ['flush_only_object' => true]);

        $this->deleteFile($sourceFileInfo);
    }

    /**
     * @param ChannelInterface $channel
     *
     * @throws \LogicException
     *
     * @return array
     *
     */
    protected function retrieveChannelTransformationsConfiguration(ChannelInterface $channel)
    {
        if (null === $channelConfiguration = $this->configurationRepository->findOneBy(
                ['channel' => $channel->getId()]
            )
        ) {
            throw new \LogicException(
                sprintf('No variations configuration exists for the channel "%s".', $channel->getCode())
            );
        }

        return $channelConfiguration->getConfiguration();
    }

    /**
     * Retrieve the source file of the variation and checks it's really present on the STORAGE virtual filesystem
     *
     * @param VariationInterface $variation
     *
     * @throws \LogicException
     *
     * @return FileInterface
     *
     */
    protected function retrieveSourceFile(VariationInterface $variation)
    {
        if (null === $sourceFile = $variation->getSourceFile()) {
            throw new \LogicException(sprintf('The variation "%s" has no source file.', $variation->getId()));
        }

        $storageFilesystem = $this->mountManager->getFilesystem($this->filesystemAlias);

        if (!$storageFilesystem->has($sourceFile->getKey())) {
            throw new \LogicException(
                sprintf(
                    'The source file "%s" is not present on the filesystem "%s".',
                    $sourceFile->getKey(),
                    $this->filesystemAlias
                )
            );
        }

        return $sourceFile;
    }

    /**
     * With a file called this_is_my_source_file.txt, it will return
     *      this_is_my_source_file-en_US-ecommerce.txt or
     *      this_is_my_source_file-ecommerce.txt
     *
     * @param FileInterface    $sourceFile
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return string
     */
    protected function buildVariationOutputFilename(
        FileInterface $sourceFile,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $extensionPattern = sprintf('/\.%s$/', $sourceFile->getExtension());
        $outputFileName   = preg_replace($extensionPattern, '', $sourceFile->getOriginalFilename());

        if (null !== $locale) {
            $outputFileName = sprintf('%s-%s', $outputFileName, $locale->getCode());
        }

        return sprintf('%s-%s.%s', $outputFileName, $channel->getCode(), $sourceFile->getExtension());
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
    protected function deleteFile(\SplFileInfo $file)
    {
        $fs = new Filesystem();
        $fs->remove($file->getPathname());
    }
}
