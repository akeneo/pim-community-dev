<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    ArchivableWriterInterface
{
    private const DATETIME_FORMAT = 'Y-m-d_H-i-s';

    private FlatItemBufferFlusher $flusher;
    private BufferFactory $bufferFactory;
    private Filesystem $localFileSystem;

    /** @var ?FlatItemBuffer<array> */
    private ?FlatItemBuffer $flatRowBuffer = null;

    /** @var WrittenFileInfo[] */
    private array $writtenFiles = [];

    protected ?StepExecution $stepExecution = null;

    public function __construct(
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        Filesystem $localFileSystem
    ) {
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->localFileSystem = $localFileSystem;
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
            $this->localFileSystem->mkdir($exportDirectory);
        }
    }

    /**
     * {@inheritdoc}
     * @param array<array> $items
     */
    public function write(array $items): void
    {
        $parameters = $this->getStepExecution()->getJobParameters();

        $this->getFlatRowBuffer()->write($items, [
            'withHeader' => $parameters->get('withHeader'),
        ]);
    }

    /**
     * Flush items into a file
     */
    public function flush(): void
    {
        $this->flusher->setStepExecution($this->getStepExecution());

        $parameters = $this->getStepExecution()->getJobParameters();
        $writtenFiles = $this->flusher->flush(
            $this->getFlatRowBuffer(),
            $this->getWriterConfiguration(),
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile(
                $writtenFile,
                \basename($writtenFile)
            );
        }
    }

    /**
     * Get the file path in which to write the data
     */
    public function getPath(): string
    {
        $parameters = $this->getStepExecution()->getJobParameters();
        $jobExecution = $this->getStepExecution()->getJobExecution();
        $filePath = $parameters->get('filePath');

        if (!str_contains($filePath, '%')) {
            return $filePath;
        }

        $jobLabel = '';
        $datetime = $this->getStepExecution()->getStartTime()->format(self::DATETIME_FORMAT);
        if (null !== $jobExecution->getJobInstance()) {
            $jobLabel = preg_replace('#[^A-Za-z0-9\.]#', '_', $jobExecution->getJobInstance()->getLabel());
        }

        $replacePairs = ['%datetime%' => $datetime, '%job_label%' => $jobLabel];

        return strtr($filePath, $replacePairs);
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
     * @return array<string, mixed>
     */
    abstract protected function getWriterConfiguration(): array;

    private function getStepExecution(): StepExecution
    {
        if (!$this->stepExecution instanceof StepExecution) {
            throw new \Exception('Reader have not been properly initialized');
        }

        return $this->stepExecution;
    }

    /**
     * @return FlatItemBuffer<array>
     */
    private function getFlatRowBuffer(): FlatItemBuffer
    {
        if (!$this->flatRowBuffer instanceof FlatItemBuffer) {
            throw new \Exception('FlatRowBuffer have not been properly initialized');
        }

        return $this->flatRowBuffer;
    }
}
