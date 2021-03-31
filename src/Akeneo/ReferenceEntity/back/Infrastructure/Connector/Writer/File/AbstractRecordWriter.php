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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Writer\File;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;

abstract class AbstractRecordWriter extends AbstractFileWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface
{
    private ArrayConverterInterface $arrayConverter;
    private BufferFactory $bufferFactory;
    private FlatItemBufferFlusher $flusher;
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;
    private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels;
    private FileExporterPathGeneratorInterface $fileExporterPath;
    private FileInfoRepositoryInterface $fileInfoRepository;
    private FilesystemProvider $filesystemProvider;

    private ?FlatItemBuffer $flatRowBuffer = null;
    /** @var AbstractAttribute[] */
    private array $attributesIndexedByIdentifier;

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->fileExporterPath = $fileExporterPath;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->filesystemProvider = $filesystemProvider;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }

        $referenceEntityIdentifier = $this->stepExecution->getJobParameters()->get('reference_entity_identifier');
        $this->attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
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
            $flatItems[] = $this->arrayConverter->convert($item);
        }

        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($flatItems, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        if (true === $this->stepExecution->getJobParameters()->get('withHeader')) {
            $this->flatRowBuffer->addToHeaders($this->getAdditionalHeaders());
        }
        $this->flusher->setStepExecution($this->stepExecution);
        $parameters = $this->stepExecution->getJobParameters();
        $writtenFlatFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->getWriterConfiguration(),
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFlatFiles as $writtenFlatFile) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile(
                $writtenFlatFile,
                \basename($writtenFlatFile)
            );
        }
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
            if (!$attribute instanceof ImageAttribute) {
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

            $fileKey = $normalizedValue['data']['filePath'] ?? null;
            if (\is_string($fileKey)) {
                $fileInfo = $this->fileInfoRepository->findOneByIdentifier($fileKey);
                if ($fileInfo instanceof FileInfoInterface) {
                    $outputFilePath = \sprintf('%s%s', $exportDirectory, $fileInfo->getOriginalFilename());
                    $filesystem = $this->filesystemProvider->getFilesystem($fileInfo->getStorage());
                    if (!$filesystem->has($fileInfo->getKey())) {
                        $this->stepExecution->addWarning(
                            'The media has not been found or is not currently available',
                            [],
                            new DataInvalidItem(
                                [
                                    'from' => $fileInfo->getKey(),
                                    'to' => [
                                        'filePath' => \dirname($outputFilePath),
                                        'filename' => \basename($outputFilePath),
                                    ],
                                    'storage' => $fileInfo->getStorage(),
                                ]
                            )
                        );
                    } else {
                        $item['values'][$index]['data']['filePath'] = $exportDirectory . $fileInfo->getOriginalFilename();
                        $this->writtenFiles[] = WrittenFileInfo::fromFileStorage(
                            $fileInfo->getKey(),
                            $fileInfo->getStorage(),
                            $exportDirectory . $fileInfo->getOriginalFilename()
                        );
                    }
                }
            }
        }

        return $item;
    }

    /**
     * Gets all possible headers from family attributes, in case no record has the corresponding values set
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
}
