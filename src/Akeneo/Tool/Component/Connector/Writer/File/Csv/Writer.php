<?php

namespace Akeneo\Tool\Component\Connector\Writer\File\Csv;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Item\PausableItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobProgress\ItemWriterState;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use League\Flysystem\FilesystemOperator;

/**
 * Write data into a csv file on the filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Writer extends AbstractFileWriter implements ItemWriterInterface, InitializableInterface, FlushableInterface, PausableItemWriterInterface
{
    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var FlatItemBuffer */
    protected $flatRowBuffer = null;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var BufferFactory */
    protected $bufferFactory;

    /** @var array */
    protected $headers = [];

    /**
     * @param ArrayConverterInterface $arrayConverter
     * @param BufferFactory           $bufferFactory
     * @param FlatItemBufferFlusher   $flusher
     */
    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        private ?FilesystemOperator $filesystemOperator = null
    ) {
        parent::__construct();

        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $path = null;
        $writerState = $this->stepExecution->getRawState()['writer'] ?? null;

        if (null !== $writerState) {
            $content = $this->filesystemOperator->read($writerState['flat_buffer_file_path']);

            $path = tempnam(sys_get_temp_dir(), 'akeneo_buffer_');
            file_put_contents($path, $content);
        }

        $this->flatRowBuffer = $this->bufferFactory->create($path);
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
        foreach ($items as $item) {
            $flatItems[] = $this->arrayConverter->convert($item);
        }

        $parameters = $this->stepExecution->getJobParameters();
        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($flatItems, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->flusher->setStepExecution($this->stepExecution);

        $parameters = $this->stepExecution->getJobParameters();
        $writerOptions = [
            'type'           => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM'   => false,
        ];

        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $writerOptions,
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile($writtenFile, \basename($writtenFile));
        }
    }

    public function getState(): array
    {
        if ($this->filesystemOperator) {
            $flatBufferFilePath = 'paused_job/' . $this->stepExecution->getId();
            $this->filesystemOperator->write($flatBufferFilePath, file_get_contents($this->flatRowBuffer->getFilename()));

            return [
                'flat_buffer_file_path' => $flatBufferFilePath,
            ];
        }

        return [];
    }
}
