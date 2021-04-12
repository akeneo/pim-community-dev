<?php

namespace Akeneo\Pim\TailoredExport\Connector\Writer;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface
{
    protected const DEFAULT_FILE_PATH = 'filePath';
    private const DATETIME_FORMAT = 'Y-m-d_H-i-s';

    private FlatItemBufferFlusher $flusher;
    private BufferFactory $bufferFactory;
    private FileExporterPathGeneratorInterface $fileExporterPath;
    private Filesystem $localFs;
    private string $jobParamFilePath;

    protected ?StepExecution $stepExecution;
    private ?FlatItemBuffer $flatRowBuffer;
    private array $writtenFiles = [];

    public function __construct(
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FileExporterPathGeneratorInterface $fileExporterPath,
        string $jobParamFilePath = self::DEFAULT_FILE_PATH
    ) {
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->fileExporterPath = $fileExporterPath;
        $this->jobParamFilePath = $jobParamFilePath;

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

        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');

        $this->flatRowBuffer->write($items, $options);
    }

    /**
     * Flush items into a file
     */
    public function flush(): void
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
    }

    /**
     * Get the file path in which to write the data
     */
    public function getPath(): string
    {
        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get($this->jobParamFilePath);

        if (str_contains($filePath, '%')) {
            $datetime = $this->stepExecution->getStartTime()->format(self::DATETIME_FORMAT);
            $replacePairs = ['%datetime%' => $datetime, '%job_label%' => ''];
            $jobExecution = $this->stepExecution->getJobExecution();

            if (null !== $jobExecution->getJobInstance()) {
                $jobLabel = $jobExecution->getJobInstance()->getLabel();
                $replacePairs['%job_label%'] = preg_replace('#[^A-Za-z0-9\.]#', '_', $jobLabel);
            }

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
     */
    abstract protected function getWriterConfiguration(): array;
}
