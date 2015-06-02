<?php

/**
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
use PimEnterprise\Component\ProductAsset\Builder\MetadataBuilderRegistry;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileDownloaderInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;

/**
 * Generate the variation files, store them in the filesystem and link them to the reference:
 *      - download the raw reference file from STORAGE to /tmp
 *      - generate the variation file
 *      - extract the metadata from the variation file
 *      - store the variation file in STORAGE
 *      - set the variation file to the variation and save the variation to the database
 *
 * Where STORAGE is the virtual filesystem where files are stored.
 *
 * TODO: maybe FS_STORAGE should not be hardcoded
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

    /** @var RawFileDownloaderInterface */
    protected $rawFileDownloader;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var MetadataBuilderRegistry */
    protected $metadaBuilderRegistry;

    /** @var array */
    protected $rawTransformations;

    /**
     * @param ChannelConfigurationRepositoryInterface $configurationRepository
     * @param MountManager                            $mountManager
     * @param SaverInterface                          $metadataSaver
     * @param SaverInterface                          $variationSaver
     * @param FileTransformerInterface                $fileTransformer
     * @param RawFileStorerInterface                  $rawFileStorer
     * @param RawFileDownloaderInterface              $rawFileDownloader
     * @param MetadataBuilderRegistry                 $metadaBuilderRegistry
     */
    public function __construct(
        ChannelConfigurationRepositoryInterface $configurationRepository,
        MountManager $mountManager,
        SaverInterface $metadataSaver,
        SaverInterface $variationSaver,
        FileTransformerInterface $fileTransformer,
        RawFileStorerInterface $rawFileStorer,
        RawFileDownloaderInterface $rawFileDownloader,
        MetadataBuilderRegistry $metadaBuilderRegistry
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->fileTransformer         = $fileTransformer;
        $this->mountManager            = $mountManager;
        $this->metadataSaver           = $metadataSaver;
        $this->variationSaver          = $variationSaver;
        $this->rawFileStorer           = $rawFileStorer;
        $this->rawFileDownloader       = $rawFileDownloader;
        $this->metadaBuilderRegistry   = $metadaBuilderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromAsset(
        AssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        if (null === $reference = $asset->getReference($locale)) {
            if (null === $locale) {
                $msg = sprintf('The asset "%s" has no reference without locale.', $asset->getCode());
            } else {
                $msg = sprintf(
                    'The asset "%s" has no reference for the locale "%s".',
                    $asset->getCode(),
                    $locale->getCode()
                );
            }

            throw new \LogicException($msg);
        }

        $this->generateFromReference($reference, $channel, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromReference(
        ReferenceInterface $reference,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        //TODO: check couple reference/locale

        $this->rawTransformations = $this->retrieveChannelTransformationsConfiguration($channel);
        $variation                = $this->retrieveVariation($reference, $channel);
        $referenceFile            = $this->retrieveReferenceFile($reference);
        $outputFileName           = $this->buildVariationOutputFilename($referenceFile, $channel, $locale);

        $this->generateFromFile($referenceFile, $variation, $channel, $outputFileName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromFile(
        FileInterface $inputFile,
        VariationInterface $variation,
        ChannelInterface $channel,
        $outputFilename,
        $setVariationToLocked = false
    ) {
        if (null === $this->rawTransformations) {
            $this->rawTransformations = $this->retrieveChannelTransformationsConfiguration($channel);
        }

        $storageFilesystem = $this->mountManager->getFilesystem(ProductAssetFileSystems::FS_STORAGE);
        $inputFileInfo     = $this->rawFileDownloader->download($inputFile, $storageFilesystem);
        $variationFileInfo = $this->fileTransformer->transform(
            $inputFileInfo,
            $this->rawTransformations,
            $outputFilename
        );
        $variationMetadata = $this->extractMetadata($variationFileInfo);
        $variationFile     = $this->rawFileStorer->store($variationFileInfo, ProductAssetFileSystems::FS_STORAGE);

        $variationMetadata->setFile($variationFile);
        $this->metadataSaver->save($variationMetadata);

        $variation->setFile($variationFile);
        $variation->setLocked($setVariationToLocked);
        $this->variationSaver->save($variation);

        //TODO: use symfony SF component
        unlink($inputFileInfo->getPathname());
    }

    /**
     * @param ChannelInterface $channel
     *
     * @return array
     *
     * @throws \LogicException
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

        $configuration = $channelConfiguration->getConfiguration();

        return $configuration['pipeline'];
    }

    /**
     * @param ReferenceInterface $reference
     * @param ChannelInterface   $channel
     *
     * @return VariationInterface
     *
     * @throws \LogicException
     */
    protected function retrieveVariation(ReferenceInterface $reference, ChannelInterface $channel)
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
     * @param ReferenceInterface $reference
     *
     * @return FileInterface
     *
     * @throws \LogicException
     */
    protected function retrieveReferenceFile(ReferenceInterface $reference)
    {
        if (null === $referenceFile = $reference->getFile()) {
            throw new \LogicException(sprintf('The reference "%s" has no file.', $reference->getId()));
        }

        //TODO: should be deleted if FS_STORAGE is not be hardcoded
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
     * @param FileInterface    $referenceFile
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return string
     */
    protected function buildVariationOutputFilename(
        FileInterface $referenceFile,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $extensionPattern = sprintf('/\.%s$/', $referenceFile->getExtension());
        $outputFileName   = preg_replace($extensionPattern, '', $referenceFile->getOriginalFilename());

        if (null !== $locale) {
            $outputFileName = sprintf('%s-%s', $outputFileName, $locale->getCode());
        }

        return sprintf('%s-%s.%s', $outputFileName, $channel->getCode(), $referenceFile->getExtension());
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
}
