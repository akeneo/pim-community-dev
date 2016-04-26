<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;

/**
 * XLSX VariantGroup writer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxVariantGroupWriter extends AbstractFileWriter implements ItemWriterInterface, ArchivableWriterInterface
{
    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var BulkFileExporter */
    protected $fileExporter;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /** @var array */
    protected $writtenFiles;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param BulkFileExporter          $fileExporter
     * @param ColumnSorterInterface     $columnSorter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $fileExporter,
        ColumnSorterInterface $columnSorter
    ) {
        parent::__construct($filePathResolver);

        $this->flatRowBuffer = $flatRowBuffer;
        $this->fileExporter  = $fileExporter;
        $this->columnSorter  = $columnSorter;
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

        $variantGroups = $media = [];
        foreach ($items as $item) {
            $variantGroups[] = $item['variant_group'];
            $media[]         = $item['media'];
        }

        $parameters = $this->stepExecution->getJobParameters();
        $withHeader = $parameters->getParameter('withHeader');
        $this->flatRowBuffer->write($variantGroups, $withHeader);
        $this->fileExporter->exportAll($media, $exportDirectory);

        foreach ($this->fileExporter->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->fileExporter->getErrors() as $error) {
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
