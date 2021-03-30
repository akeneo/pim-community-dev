<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    ArchivableWriterInterface
{
    protected const DEFAULT_FILE_PATH = 'filePath';

    protected ArrayConverterInterface $arrayConverter;
    protected FlatItemBufferFlusher $flusher;
    protected BufferFactory $bufferFactory;
    protected AttributeRepositoryInterface $attributeRepository;
    protected FileExporterPathGeneratorInterface $fileExporterPath;
    private FlatTranslatorInterface $flatTranslator;
    private FileInfoRepositoryInterface $fileInfoRepository;
    private FilesystemProvider $filesystemProvider;
    /** @var string[] */
    protected array $mediaAttributeTypes;
    protected string $jobParamFilePath;

    protected ?StepExecution $stepExecution = null;

    protected Filesystem $localFs;
    protected ?FlatItemBuffer $flatRowBuffer = null;
    /** @var WrittenFileInfo[] */
    protected array $writtenFiles = [];
    protected string $datetimeFormat = 'Y-m-d_H-i-s';

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        FlatTranslatorInterface $flatTranslator,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        array $mediaAttributeTypes,
        string $jobParamFilePath = self::DEFAULT_FILE_PATH
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->attributeRepository = $attributeRepository;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->fileExporterPath = $fileExporterPath;
        $this->jobParamFilePath = $jobParamFilePath;
        $this->flatTranslator = $flatTranslator;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->filesystemProvider = $filesystemProvider;

        $this->localFs = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $parameters = $this->stepExecution->getJobParameters();
        $converterOptions = $this->getConverterOptions($parameters);

        $flatItems = [];
        foreach ($items as $item) {
            if ($parameters->has('with_media') && $parameters->get('with_media')) {
                $item = $this->resolveMediaPaths($item);
            }

            $flatItems[] = $this->arrayConverter->convert($item, $converterOptions);
        }

        if (!empty($items) && $parameters->has('withHeader') && true === $parameters->get('withHeader')) {
            $flatItems = $this->fillMissingFlatItemValues($flatItems);
        }

        if ($parameters->has('with_label') && $parameters->get('with_label') && $parameters->has('file_locale')) {
            $fileLocale = $parameters->get('file_locale');
            $headerWithLabel = $parameters->has('header_with_label') && $parameters->get('header_with_label');
            $scope = $parameters->get('filters')['structure']['scope'] ?? $parameters->get('scope');

            $flatItems = $this->flatTranslator->translate($flatItems, $fileLocale, $scope, $headerWithLabel);
        }

        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');

        $this->flatRowBuffer->write($flatItems, $options);
    }

    private function fillMissingFlatItemValues(array $items): array
    {
        $additionalHeaders = $this->getAdditionalHeaders();
        $additionalHeadersFilled = array_fill_keys($additionalHeaders, '');

        $flatItemIndex = array_keys($items);
        $additionalHeadersFilledInFlatItemFormat = array_fill_keys($flatItemIndex, $additionalHeadersFilled);

        return array_replace_recursive($additionalHeadersFilledInFlatItemFormat, $items);
    }

    protected function getAdditionalHeaders(): array
    {
        return [];
    }

    /**
     * Flush items into a file
     */
    public function flush(): void
    {
        $this->flusher->setStepExecution($this->stepExecution);

        $parameters = $this->stepExecution->getJobParameters();

        $flatFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->getWriterConfiguration(),
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($flatFiles as $flatFile) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile(
                $flatFile,
                \basename($flatFile)
            );
        }
    }

    /**
     * Get the file path in which to write the data
     *
     * @param array $placeholders
     *
     * @return string
     */
    public function getPath(array $placeholders = []): string
    {
        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get($this->jobParamFilePath);

        if (false !== \strpos($filePath, '%')) {
            $datetime = $this->stepExecution->getStartTime()->format($this->datetimeFormat);
            $defaultPlaceholders = ['%datetime%' => $datetime, '%job_label%' => ''];
            $jobExecution = $this->stepExecution->getJobExecution();

            if (isset($placeholders['%job_label%'])) {
                $placeholders['%job_label%'] = $this->sanitize($placeholders['%job_label%']);
            } elseif (null !== $jobExecution->getJobInstance()) {
                $defaultPlaceholders['%job_label%'] = $this->sanitize($jobExecution->getJobInstance()->getLabel());
            }
            $replacePairs = array_merge($defaultPlaceholders, $placeholders);
            $filePath = strtr($filePath, $replacePairs);
        }

        return $filePath;
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
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Get configuration for writer (type of export, delimiter, enclosure, etc)
     *
     * @return array
     */
    abstract protected function getWriterConfiguration(): array;

    /**
     * Return the identifier of the item (e.q sku or variant group code)
     *
     * @param array $item
     *
     * @return string
     */
    abstract protected function getItemIdentifier(array $item): string;

    /**
     * - Add the media to the $this->writtenFiles to be archived later
     * - Update the value of each media in the standard format to add the final path of media in archive.
     *
     * The standard format for a media contains only the filePath (which is the unique key of the media):
     * {
     *     "values": {
     *         "picture": [
     *              {
     *                  "locale": "en_US",
     *                  "scope": "ecommerce",
     *                  "data": "a/b/c/d/e/it_s_my_filename.jpg"
     *              }
     *          ]
     *     }
     * }
     *
     * In exported files, we don't want to see the key, but the original filename. As the standard format does not
     * contain this information, we use the FileInfoRepository to find this information
     *
     * After:
     * {
     *     "values": {
     *         "picture": [
     *              {
     *                  "locale": "en_US",
     *                  "scope": "ecommerce",
     *                  "data": "files/item_identifier/picture/en_US/ecommerce/it's my filename.jpg"
     *              }
     *          ]
     *     }
     * }
     *
     * @param array $item standard format of an item
     *
     * @return array
     */
    final protected function resolveMediaPaths(array $item): array
    {
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($item['values']));
        $mediaAttributeTypes = array_filter(
            $attributeTypes,
            function ($attributeCode) {
                return in_array($attributeCode, $this->mediaAttributeTypes);
            }
        );
        $identifier = $this->getItemIdentifier($item);

        foreach ($mediaAttributeTypes as $attributeCode => $attributeType) {
            if (!isset($item['values'][$attributeCode])) {
                continue;
            }

            foreach ($item['values'][$attributeCode] as $index => $value) {
                if (null !== $value['data']) {
                    $exportDirectory = $this->fileExporterPath->generate(
                        $value,
                        [
                            'identifier' => $identifier,
                            'code' => $attributeCode,
                        ]
                    );

                    if (array_key_exists('paths', $value)) {
                        $paths = [];
                        foreach ($value['paths'] as $fileKey) {
                            $writtenFile = $this->checkMediaFile($fileKey, $exportDirectory);
                            if (null !== $writtenFile) {
                                $paths[] = $writtenFile->outputFilepath();
                                $this->writtenFiles[] = $writtenFile;
                            }
                        }
                        $item['values'][$attributeCode][$index]['paths'] = $paths;
                    } else {
                        $writtenFile = $this->checkMediaFile($value['data'], $exportDirectory);
                        if (null !== $writtenFile) {
                            $item['values'][$attributeCode][$index]['data'] = $writtenFile->outputFilepath();
                            $this->writtenFiles[] = $writtenFile;
                        }
                    }
                }
            }
        }

        return $item;
    }

    /**
     * @param JobParameters $parameters
     *
     * @return array
     */
    protected function getConverterOptions(JobParameters $parameters): array
    {
        $options = [];

        if ($parameters->has('decimalSeparator')) {
            $options['decimal_separator'] = $parameters->get('decimalSeparator');
        }

        if ($parameters->has('dateFormat')) {
            $options['date_format'] = $parameters->get('dateFormat');
        }

        if ($parameters->has('ui_locale')) {
            $options['locale'] = $parameters->get('ui_locale');
        }

        return $options;
    }

    /**
     * Replace [^A-Za-z0-9\.] from a string by '_'
     *
     * @param string $value
     *
     * @return string
     */
    protected function sanitize(string $value): string
    {
        return preg_replace('#[^A-Za-z0-9\.]#', '_', $value);
    }

    private function checkMediaFile(string $key, string $outputDirectory): ?WrittenFileInfo
    {
        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($key);
        if (null === $fileInfo) {
            return null;
        }

        $outputFilePath = \sprintf('%s%s', $outputDirectory, $fileInfo->getOriginalFilename());
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

            return null;
        }

        return WrittenFileInfo::fromFileStorage(
            $fileInfo->getKey(),
            $fileInfo->getStorage(),
            $outputFilePath,
        );
    }
}
