<?php

namespace Pim\Component\Connector\Writer\File;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;

/**
 * Write simple data into a XLSX file on the local filesystem
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxSimpleWriter extends AbstractFileWriter
{
    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param ColumnSorterInterface     $columnSorter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        ColumnSorterInterface $columnSorter
    ) {
        parent::__construct($filePathResolver);

        $this->flatRowBuffer = $flatRowBuffer;
        $this->columnSorter = $columnSorter;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $exportFolder = dirname($this->getPath());
        if (!is_dir($exportFolder)) {
            $this->localFs->mkdir($exportFolder);
        }

        $parameters = $this->stepExecution->getJobParameters();
        $withHeader = $parameters->get('withHeader');
        $this->flatRowBuffer->write($items, $withHeader);
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
    }
}
