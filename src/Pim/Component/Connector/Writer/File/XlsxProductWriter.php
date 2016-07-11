<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;

/**
 * Write product data into a XLSX file on the local filesystem
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxProductWriter extends AbstractFileWriter implements ItemWriterInterface, ArchivableWriterInterface
{
    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var array */
    protected $writtenFiles;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var BulkFileExporter */
    protected $fileExporter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param BulkFileExporter          $fileExporter
     * @param FlatItemBufferFlusher     $flusher
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $fileExporter,
        FlatItemBufferFlusher $flusher
    ) {
        parent::__construct($filePathResolver);

        $this->flatRowBuffer = $flatRowBuffer;
        $this->fileExporter = $fileExporter;
        $this->flusher = $flusher;
        $this->writtenFiles = [];
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = $media = [];
        foreach ($items as $item) {
            $products[] = $item['product'];
            $media[] = $item['media'];
        }

        $parameters = $this->stepExecution->getJobParameters();
        $withHeader = $parameters->get('withHeader');
        $this->flatRowBuffer->write($products, $withHeader);

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        if ($parameters->has('with_media') && !$parameters->get('with_media')) {
            return;
        }

        $this->fileExporter->exportAll($media, $exportDirectory);

        foreach ($this->fileExporter->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->fileExporter->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $error['message'],
                [],
                $error['medium']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->flusher->setStepExecution($this->stepExecution);

        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->getPath(),
            $this->stepExecution->getJobParameters()->get('linesPerFile'),
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
