<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractUserWriter extends AbstractFileWriter implements
    InitializableInterface,
    FlushableInterface,
    ArchivableWriterInterface
{
    private ArrayConverterInterface $arrayConverter;
    private BufferFactory $bufferFactory;
    private FlatItemBufferFlusher $flusher;
    private ?FlatItemBuffer $flatRowBuffer = null;

    private array $writtenFiles = [];

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        Filesystem $localFs
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->localFs = $localFs;
    }

    /**
     * {@inheritdoc}
     */
    final public function initialize(): void
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getWrittenFiles(): array
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    final public function write(array $items): void
    {
        $exportDirectory = dirname($this->getPath());
        $this->localFs->mkdir($exportDirectory);

        $flatItems = [];
        $workingDirectory = \rtrim(
            $this->stepExecution->getJobExecution()->getExecutionContext()->get(
                JobInterface::WORKING_DIRECTORY_PARAMETER
            ),
            DIRECTORY_SEPARATOR
        );

        foreach ($items as $item) {
            $avatarPath = $item['avatar']['filePath'] ?? null;
            if (null !== $avatarPath) {
                $fullPath = \sprintf('%s/%s', $workingDirectory, $avatarPath);
                if ($this->localFs->exists($fullPath)) {
                    $this->writtenFiles[$fullPath] = $avatarPath;
                }
            }
            $flatItems[] = $this->arrayConverter->convert($item, []);
        }

        $this->flatRowBuffer->write(
            $flatItems,
            ['withHeader' => $this->stepExecution->getJobParameters()->get('withHeader')]
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function flush(): void
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

    abstract protected function getWriterConfiguration(): array;
}
