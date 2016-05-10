<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;

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

    /** @var BulkFileExporter */
    protected $mediaCopier;

    /** @var array */
    protected $writtenFiles;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param BulkFileExporter          $mediaCopier
     * @param ColumnSorterInterface     $columnSorter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $mediaCopier,
        ColumnSorterInterface $columnSorter
    ) {
        parent::__construct($filePathResolver);

        $this->flatRowBuffer = $flatRowBuffer;
        $this->mediaCopier   = $mediaCopier;
        $this->columnSorter  = $columnSorter;
        $this->writtenFiles  = [];
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

        $products = $media = [];
        foreach ($items as $item) {
            $products[] = $item['product'];
            $media[]    = $item['media'];
        }

        $parameters = $this->stepExecution->getJobParameters();
        $withHeader = $parameters->getParameter('withHeader');
        $this->flatRowBuffer->write($products, $withHeader);
        $this->mediaCopier->exportAll($media, $exportDirectory);

        foreach ($this->mediaCopier->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->mediaCopier->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $this->getName(),
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
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($this->getPath());

        $headers = $this->columnSorter->sort($this->flatRowBuffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');
        $writer->addRow($headers);
        foreach ($this->flatRowBuffer->getBuffer() as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        $writer->close();
        $this->writtenFiles[$this->getPath()] = basename($this->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }
}
