<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\File;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Symfony\Component\Finder\Finder;

abstract class AbstractAssetWriter extends AbstractFileWriter implements InitializableInterface, FlushableInterface, ArchivableWriterInterface
{
    /** @var ArrayConverterInterface */
    private $arrayConverter;

    /** @var BufferFactory */
    private $bufferFactory;

    /** @var FlatItemBufferFlusher */
    private $flusher;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var FindActivatedLocalesPerChannelsInterface */
    private $findActivatedLocalesPerChannels;

    /** @var FileExporterPathGeneratorInterface */
    private $fileExporterPath;

    /** @var FlatItemBuffer */
    private $flatRowBuffer;

    /** @var AbstractAttribute[] */
    private $attributesIndexedByIdentifier;

    /** @var array */
    private $writtenFiles = [];

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        FileExporterPathGeneratorInterface $fileExporterPath
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->fileExporterPath = $fileExporterPath;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }

        $assetFamilyIdentifier = $this->stepExecution->getJobParameters()->get('asset_family_identifier');
        $this->attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles(): array
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        $flatItems = [];
        $parameters = $this->stepExecution->getJobParameters();
        $directory = $this->stepExecution->getJobExecution()->getExecutionContext()->get(
            JobInterface::WORKING_DIRECTORY_PARAMETER
        );

        foreach ($items as $item) {
            if ($parameters->has('with_media') && $parameters->get('with_media')) {
                $item = $this->resolveMediaPaths($item, $directory);
            }
            $flatItems[] = $this->arrayConverter->convert($item, [
                'with_prefix_suffix' => $this->stepExecution->getJobParameters()->get('with_prefix_suffix')
            ]);
        }

        $parameters = $this->stepExecution->getJobParameters();
        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($flatItems, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (true === $this->stepExecution->getJobParameters()->get('withHeader')) {
            $this->flatRowBuffer->addToHeaders($this->getAdditionalHeaders());
        }
        $this->flusher->setStepExecution($this->stepExecution);
        $parameters = $this->stepExecution->getJobParameters();
        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->getWriterConfiguration(),
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[$writtenFile] = basename($writtenFile);
        }

        $this->exportMedias();
    }

    abstract protected function getWriterConfiguration(): array;

    /**
     * - Add the media to the $this->writtenFiles to be archived later
     * - Update the value of each media in the standard format to add the final path of media in archive.
     */
    private function resolveMediaPaths(array $item, string $tmpDirectory): array
    {
        $identifier = $item['code'];

        foreach ($item['values'] as $index => $normalizedValue) {
            $attribute = $this->attributesIndexedByIdentifier[$normalizedValue['attribute']] ?? null;
            if (!$attribute instanceof MediaFileAttribute) {
                continue;
            }

            $exportDirectory = $this->fileExporterPath->generate(
                [
                    'scope' => $normalizedValue['channel'],
                    'locale' => $normalizedValue['locale'],
                ],
                [
                    'identifier' => $identifier,
                    'code' => $attribute->getCode()->__toString(),
                ]
            );

            $finder = new Finder();
            if (is_dir($tmpDirectory . $exportDirectory)) {
                $files = iterator_to_array($finder->files()->in($tmpDirectory . $exportDirectory));
                if (!empty($files)) {
                    $path = $exportDirectory . current($files)->getFilename();
                    $this->writtenFiles[$tmpDirectory . $path] = $path;
                    $item['values'][$index]['data']['filePath'] = $path;
                }
            }
        }

        return $item;
    }

    /**
     * Gets all possible headers from family attributes, in case no asset has the corresponding values set
     */
    private function getAdditionalHeaders(): array
    {
        $localesPerChannel = $this->findActivatedLocalesPerChannels->findAll();
        $activeChannelCodes = array_keys($localesPerChannel);
        $activeLocaleCodes = array_unique(array_merge(...array_values($localesPerChannel)));

        $headers = [];
        foreach ($this->attributesIndexedByIdentifier as $attribute) {
            if ($attribute->hasValuePerChannel() && $attribute->hasValuePerLocale()) {
                foreach ($localesPerChannel as $channelCode => $localeCodes) {
                    foreach ($localeCodes as $localeCode) {
                        $headers[] = sprintf(
                            '%s-%s-%s',
                            $attribute->getCode()->__toString(),
                            $localeCode,
                            $channelCode
                        );
                    }
                }
            } elseif ($attribute->hasValuePerChannel()) {
                foreach ($activeChannelCodes as $channelCode) {
                    $headers[] = sprintf('%s-%s', $attribute->getCode()->__toString(), $channelCode);
                }
            } elseif ($attribute->hasValuePerLocale()) {
                foreach ($activeLocaleCodes as $localeCode) {
                    $headers[] = sprintf('%s-%s', $attribute->getCode()->__toString(), $localeCode);
                }
            } else {
                $headers[] = $attribute->getCode()->__toString();
            }
        }

        return $headers;
    }

    /**
     * Export medias from the working directory to the expected output directory.
     */
    private function exportMedias(): void
    {
        $outputDirectory = dirname($this->getPath());
        $workingDirectory = $this->stepExecution->getJobExecution()->getExecutionContext()->get(
            JobInterface::WORKING_DIRECTORY_PARAMETER
        );

        $outputFilesDirectory = $outputDirectory . DIRECTORY_SEPARATOR . 'files';
        $workingFilesDirectory = $workingDirectory . 'files';

        if ($this->localFs->exists($outputFilesDirectory)) {
            $this->localFs->remove($outputFilesDirectory);
        }

        if ($this->localFs->exists($workingFilesDirectory)) {
            $this->localFs->mirror($workingFilesDirectory, $outputFilesDirectory);
        }
    }
}
