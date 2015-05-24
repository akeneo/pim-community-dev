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

use Akeneo\Component\FileTransformer\FileTransformerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileDownloaderInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\ChannelVariationsConfigurationRepositoryInterface;

/**
 * Generate the variation files, store them in the filesystem and link them to the reference:
 *      - download the raw reference file from STORAGE to TMP
 *      - generate the variation file
 *      - store the variation file in STORAGE
 *      - set the variation file to the variation
 *
 * Where:
 *      - STORAGE is the virtual filesystem where files are stored
 *      - FILE_PROCESSING is the local filesystem where raw files are processed (ie: this code when be executed on
 *        the server that hosts FILE_PROCESSING)
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class VariationFileGenerator implements VariationFileGeneratorInterface
{
    /** @var ChannelVariationsConfigurationRepositoryInterface */
    protected $configurationRepository;

    /** @var MountManager */
    protected $mountManager;

    /** @var SaverInterface */
    protected $fileSaver;

    /** @var SaverInterface */
    protected $variationSaver;

    /** @var FileTransformerInterface */
    protected $fileTransformer;

    /** @var RawFileDownloaderInterface */
    protected $rawFileDownloader;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param ChannelVariationsConfigurationRepositoryInterface $repository
     * @param MountManager                                      $mountManager
     * @param SaverInterface                                    $fileSaver
     * @param SaverInterface                                    $variationSaver
     * @param FileTransformerInterface                          $fileTransformer
     * @param RawFileStorerInterface                            $rawFileStorer
     * @param RawFileDownloaderInterface                        $rawFileDownloader
     */
    public function __construct(
        ChannelVariationsConfigurationRepositoryInterface $repository,
        MountManager $mountManager,
        SaverInterface $fileSaver,
        SaverInterface $variationSaver,
        FileTransformerInterface $fileTransformer,
        RawFileStorerInterface $rawFileStorer,
        RawFileDownloaderInterface $rawFileDownloader
    ) {
        $this->configurationRepository = $repository;
        $this->fileTransformer         = $fileTransformer;
        $this->mountManager            = $mountManager;
        $this->fileSaver               = $fileSaver;
        $this->variationSaver          = $variationSaver;
        $this->rawFileStorer           = $rawFileStorer;
        $this->rawFileDownloader       = $rawFileDownloader;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromAsset(
        ProductAssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        if (null === $reference = $asset->getReference($locale)) {
            if (null === $locale) {
                $msg = sprintf(
                    'The asset "%s" has no reference for the locale "%s".',
                    $asset->getCode(),
                    $locale->getCode()
                );
            } else {
                $msg = sprintf('The asset "%s" has no reference without locale.', $asset->getCode());
            }

            throw new \LogicException($msg);
        }

        $this->generateFromReference($reference, $channel, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromReference(
        ProductAssetReferenceInterface $reference,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $configuration = $this->retrieveChannelConfiguration($channel);
        $variation     = $this->retrieveVariation($reference, $channel);
        $referenceFile = $this->retrieveReferenceFile($reference);

        $storageFilesystem = $this->mountManager->getFilesystem(ProductAssetFileSystems::FS_STORAGE);
        $referenceFileInfo = $this->rawFileDownloader->download($referenceFile, $storageFilesystem);

        //TODO: maybe we should not store the whole FileTransformer config in the channel configuration
        //TODO: (but only what's useful for us)
        //TODO: maybe the channel conf should have only ONE element (as we have only one variation file per reference)
        foreach ($configuration->getConfiguration() as $pipeline) {
            $outputFileName    = $this->buildVariationOutputFilename($referenceFileInfo, $channel, $locale);
            $variationFileInfo = $this->fileTransformer->transform(
                $referenceFileInfo,
                $pipeline['pipeline'],
                $outputFileName
            );
            $variationFile     = $this->rawFileStorer->store($variationFileInfo, ProductAssetFileSystems::FS_STORAGE);

            //TODO: extract and save variation metadata

            $this->fileSaver->save($variationFile);
            $variation->setFile($variationFile);
            $this->variationSaver->save($variation);
        }

        unlink($referenceFileInfo->getPathname());
    }


    /**
     * @param ChannelInterface $channel
     *
     * @return ChannelVariationsConfigurationInterface
     */
    protected function retrieveChannelConfiguration(ChannelInterface $channel)
    {
        if (null === $configuration = $this->configurationRepository->findOneBy(['channel' => $channel->getId()])) {
            throw new \LogicException(
                sprintf('No variations configuration exists for the channel "%s".', $channel->getCode())
            );
        }

        return $configuration;
    }

    /**
     * @param ProductAssetReferenceInterface $reference
     * @param ChannelInterface               $channel
     *
     * @return ProductAssetVariationInterface
     */
    protected function retrieveVariation(ProductAssetReferenceInterface $reference, ChannelInterface $channel)
    {
        if (null === $variation = $reference->getVariation($channel)) {
            throw new \LogicException(
                sprintf(
                    'The reference "%s" has no variation for the channel "%s".',
                    $reference->getId(),
                    $channel->getCode()
                )
            );
        }

        return $variation;
    }

    /**
     * Retrieve the reference file and checks it's really present on the STORAGE virtual filesystem
     *
     * @param ProductAssetReferenceInterface $reference
     *
     * @return FileInterface
     */
    protected function retrieveReferenceFile(ProductAssetReferenceInterface $reference)
    {
        if (null === $referenceFile = $reference->getFile()) {
            throw new \LogicException(sprintf('The reference "%s" has no file.', $reference->getId()));
        }

        if ($referenceFile->getStorage() !== ProductAssetFileSystems::FS_STORAGE) {
            throw new \LogicException(
                sprintf(
                    'Can not build a variation for a file that is not stored in the "%s" filesystem.',
                    ProductAssetFileSystems::FS_STORAGE
                )
            );
        }

        $storageFilesystem = $this->mountManager->getFilesystem(ProductAssetFileSystems::FS_STORAGE);

        if (!$storageFilesystem->has($referenceFile->getPathname())) {
            throw new \LogicException(
                sprintf('The reference file "%s" is not present on the filesystem.', $referenceFile->getPathname())
            );
        }

        return $referenceFile;
    }

    /**
     * With a file called this_is_my_reference_file.txt, it will return
     *      this_is_my_reference_file-en_US-ecommerce.txt or
     *      this_is_my_reference_file-ecommerce.txt
     *
     * @param \SplFileInfo     $referenceFileInfo
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return string
     */
    protected function buildVariationOutputFilename(
        \SplFileInfo $referenceFileInfo,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $extensionPattern = sprintf('/\.%s$/', $referenceFileInfo->getExtension());
        $outputFileName   = preg_replace($extensionPattern, '', $referenceFileInfo->getFilename());

        if (null !== $locale) {
            $outputFileName = sprintf('%s-%s', $outputFileName = $locale->getCode());
        }

        return sprintf('%s-%s.%s', $outputFileName, $channel->getCode(), $referenceFileInfo->getExtension());
    }
}
