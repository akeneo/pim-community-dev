<?php

namespace Pim\Component\Connector\Writer\File;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;

/**
 * Write simple data into a XLSX file on the local filesystem
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxSimpleWriter extends AbstractFileWriter implements ArchivableWriterInterface
{
    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /** @var int */
    protected $linesPerFile;

    /** @var int */
    protected $defaultLinesPerFile;

    /** @var array */
    protected $writtenFiles = [];

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param ColumnSorterInterface     $columnSorter
     * @param int                       $defaultLinesPerFile
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        ColumnSorterInterface $columnSorter,
        $defaultLinesPerFile
    ) {
        parent::__construct($filePathResolver);

        $this->flatRowBuffer       = $flatRowBuffer;
        $this->columnSorter        = $columnSorter;
        $this->defaultLinesPerFile = $defaultLinesPerFile;
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
        $pathPattern = $this->getPath();
        if ($this->areSeveralFilesNeeded()) {
            $pathPattern = $this->getNumberedFilePath($this->getPath());
        }

        $headers = $this->columnSorter->sort($this->flatRowBuffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

        $fileCount = 1;
        $writtenLinesCount = 0;
        foreach ($this->flatRowBuffer->getBuffer() as $count => $incompleteItem) {
            if (0 === $writtenLinesCount % $this->getLinesPerFile()) {
                $filePath = $this->resolveFilePath($pathPattern, $fileCount);

                $writtenLinesCount = 0;
                $writer = $this->getWriter($filePath);
                $writer->addRow($headers);
            }

            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);
            $writtenLinesCount++;

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }

            if (0 === $writtenLinesCount % $this->getLinesPerFile() || $this->flatRowBuffer->count() === $count + 1) {
                $writer->close();
                $this->writtenFiles[$filePath] = basename($filePath);
                $fileCount++;
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * @return bool
     */
    protected function areSeveralFilesNeeded()
    {
        return $this->flatRowBuffer->count() > $this->getLinesPerFile();
    }

    /**
     * Return the given file path in terms of the current file count if needed
     *
     * @param string $pathPattern
     * @param int    $currentFileCount
     *
     * @return string
     */
    protected function resolveFilePath($pathPattern, $currentFileCount)
    {
        $resolvedFilePath = $pathPattern;
        if ($this->areSeveralFilesNeeded()) {
            $resolvedFilePath = $this->filePathResolver->resolve(
                $pathPattern,
                array_merge_recursive(
                    $this->filePathResolverOptions,
                    ['parameters' => ['%fileNb%' => '_' . $currentFileCount]]
                )
            );
        }

        return $resolvedFilePath;
    }

    /**
     * Return the given file path with %fileNb% placeholder just before the extension of the file
     * ie: in -> '/path/myFile.txt' ; out -> '/path/myFile%fileNb%.txt'
     *
     * @param string $originalFilePath
     *
     * @return string
     */
    protected function getNumberedFilePath($originalFilePath)
    {
        $extension = '.' . pathinfo($originalFilePath, PATHINFO_EXTENSION);
        $filePath  = strstr($originalFilePath, $extension, true);

        return $filePath . '%fileNb%' . $extension;
    }

    /**
     * @param string $filePath File path to open with the writer
     *
     * @return WriterInterface
     */
    protected function getWriter($filePath)
    {
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($filePath);

        return $writer;
    }
}
