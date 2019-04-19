<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface
{
    protected const DEFAULT_FILE_PATH = 'filePath';

    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var BufferFactory */
    protected $bufferFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FileExporterPathGeneratorInterface */
    protected $fileExporterPath;

    /** @var string[] */
    protected $mediaAttributeTypes;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var Filesystem */
    protected $localFs;

    /** @var array */
    protected $writtenFiles = [];

    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var string DateTime format for the file path placeholder */
    protected $datetimeFormat = 'Y-m-d_H-i-s';

    /** @var String */
    protected $jobParamFilePath;

    /**
     * @param ArrayConverterInterface            $arrayConverter
     * @param BufferFactory                      $bufferFactory
     * @param FlatItemBufferFlusher              $flusher
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param FileExporterPathGeneratorInterface $fileExporterPath
     * @param array                              $mediaAttributeTypes
     * @param String                             $jobParamFilePath
     */
    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
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

        $this->localFs = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
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
    public function write(array $items)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $converterOptions = $this->getConverterOptions($parameters);

        $flatItems = [];
        $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
            ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

        foreach ($items as $item) {
            if ($parameters->has('with_media') && $parameters->get('with_media')) {
                $item = $this->resolveMediaPaths($item, $directory);
            }

            $flatItems[] = $this->arrayConverter->convert($item, $converterOptions);
        }

        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($flatItems, $options);
    }

    /**
     * Flush items into a file
     */
    public function flush()
    {
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

    /**
     * Get the file path in which to write the data
     *
     * @param array $placeholders
     *
     * @return string
     */
    public function getPath(array $placeholders = [])
    {
        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get($this->jobParamFilePath);

        if (false !== strpos($filePath, '%')) {
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
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Get configuration for writer (type of export, delimiter, enclosure, etc)
     *
     * @return array
     */
    abstract protected function getWriterConfiguration();

    /**
     * Return the identifier of the item (e.q sku or variant group code)
     *
     * @param array $item
     *
     * @return string
     */
    abstract protected function getItemIdentifier(array $item);

    /**
     * - Add the media to the $this->writtenFiles to be archive later
     * - Update the value of each media in the standard format to add the final path of media in archive.
     *
     * The standard format for a media contains only the filePath (which is the unique key of the media):
     * {
     *     "values": {
     *         "picture": [
     *              {
     *                  "locale": "en_US",
     *                  "scope": "ecommerce",
     *                  "data": [
     *                      "filePath": "a/b/c/d/e/it_s_my_filename.jpg"
     *                  ]
     *              }
     *          ]
     *     }
     * }
     *
     * In exported files, we don't want to see the key, but the original filename. As the standard format does not
     * contain this information, we use the Finder() to find the media in the temporary directory created in processor.
     *
     * After:
     * {
     *     "values": {
     *         "picture": [
     *              {
     *                  "locale": "en_US",
     *                  "scope": "ecommerce",
     *                  "data": [
     *                      "filePath": "files/item_identifier/picture/en_US/ecommerce/it's my filename.jpg"
     *                  ]
     *              }
     *          ]
     *     }
     * }
     *
     * @param array  $item          standard format of an item
     * @param string $tmpDirectory  directory where media have been copied before to be exported
     *
     * @return array
     */
    protected function resolveMediaPaths(array $item, $tmpDirectory)
    {
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($item['values']));
        $mediaAttributeTypes = array_filter($attributeTypes, function ($attributeCode) {
            return in_array($attributeCode, $this->mediaAttributeTypes);
        });

        $identifier = $this->getItemIdentifier($item);

        foreach ($mediaAttributeTypes as $attributeCode => $attributeType) {
            if (!isset($item['values'][$attributeCode])) {
                continue;
            }

            foreach ($item['values'][$attributeCode] as $index => $value) {
                if (null !== $value['data']) {
                    $exportDirectory = $this->fileExporterPath->generate($value, [
                        'identifier' => $identifier,
                        'code'       => $attributeCode,
                    ]);

                    $finder = new Finder();
                    if (is_dir($tmpDirectory . $exportDirectory)) {
                        $files = iterator_to_array($finder->files()->in($tmpDirectory . $exportDirectory));
                        if (!empty($files)) {
                            $path = $exportDirectory . current($files)->getFilename();
                            $this->writtenFiles[$tmpDirectory . $path] = $path;
                            $item['values'][$attributeCode][$index]['data'] = $path;
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
    protected function getConverterOptions(JobParameters $parameters)
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
     * Export medias from the working directory to the output expected directory.
     *
     * Basically, we first remove the content of /path/where/my/user/expects/the/export/files/.
     * (This path can exist of an export was launched previously)
     *
     * Then we copy /path/of/the/working/directory/files/ to /path/where/my/user/expects/the/export/files/.
     */
    protected function exportMedias()
    {
        $outputDirectory = dirname($this->getPath());
        $workingDirectory = $this->stepExecution->getJobExecution()->getExecutionContext()
            ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

        $outputFilesDirectory = $outputDirectory . DIRECTORY_SEPARATOR . 'files';
        $workingFilesDirectory = $workingDirectory . 'files';

        if ($this->localFs->exists($outputFilesDirectory)) {
            $this->localFs->remove($outputFilesDirectory);
        }

        if ($this->localFs->exists($workingFilesDirectory)) {
            $this->localFs->mirror($workingFilesDirectory, $outputFilesDirectory);
        }
    }

    /**
     * Replace [^A-Za-z0-9\.] from a string by '_'
     *
     * @param string $value
     *
     * @return string
     */
    protected function sanitize($value)
    {
        return preg_replace('#[^A-Za-z0-9\.]#', '_', $value);
    }
}
