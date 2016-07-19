<?php

namespace Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Buffer\BufferFactory;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;

/**
 * CSV variant group writer
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupWriter extends AbstractFileWriter implements ItemWriterInterface, ArchivableWriterInterface
{
    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var BulkFileExporter */
    protected $fileExporter;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var array */
    protected $writtenFiles = [];

    /** @var BufferFactory */
    protected $bufferFactory;

    /**
     * @param BufferFactory             $bufferFactory
     * @param BulkFileExporter          $fileExporter
     * @param FlatItemBufferFlusher     $flusher
     */
    public function __construct(
        BufferFactory $bufferFactory,
        BulkFileExporter $fileExporter,
        FlatItemBufferFlusher $flusher
    ) {
        parent::__construct();

        $this->bufferFactory = $bufferFactory;
        $this->fileExporter = $fileExporter;
        $this->flusher = $flusher;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }
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

        $variantGroups = $media = $options = [];
        foreach ($items as $item) {
            $variantGroups[] = $item['variant_group'];
            $media[]         = $item['media'];
        }

        $parameters = $this->stepExecution->getJobParameters();
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($variantGroups, $options);
        $this->fileExporter->exportAll($media, $exportDirectory);

        foreach ($this->fileExporter->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->fileExporter->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $error['message'],
                [],
                new DataInvalidItem($error['medium'])
            );
        }
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
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
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1),
            $this->filePathResolverOptions
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[$writtenFile] = basename($writtenFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }
}
