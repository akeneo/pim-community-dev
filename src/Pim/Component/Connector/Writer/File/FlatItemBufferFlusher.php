<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;

/**
 * Flushes the flat item buffer into one or multiple output files.
 * @see Pim\Component\Connector\Writer\File\FlatItemBuffer
 *
 * Several output files are created if the buffer contains more items that maximum lines authorized per output file.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatItemBufferFlusher implements StepExecutionAwareInterface
{
    /** @var FilePathResolverInterface */
    protected $filePathResolver;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param ColumnSorterInterface     $columnSorter
     */
    public function __construct(FilePathResolverInterface $filePathResolver, ColumnSorterInterface $columnSorter = null)
    {
        $this->filePathResolver = $filePathResolver;
        $this->columnSorter = $columnSorter;
    }

    /**
     * Flushes the flat item buffer into one or multiple output files.
     * Several output files are created if the buffer contains more items that maximum lines authorized per output file.
     *
     * @param FlatItemBuffer $buffer
     * @param int            $maxLinesPerFile by default -1, which means there is no limit of lines
     * @param string         $basePathname
     * @param array          $filePathResolverOptions
     *
     * @return array the absolute pathnames of files that have been written
     */
    public function flush(FlatItemBuffer $buffer, $basePathname, $maxLinesPerFile = -1, array $filePathResolverOptions = [])
    {
        $writtenFiles = [];

        $basePathPattern = $basePathname;
        if ($this->areSeveralFilesNeeded($buffer, $maxLinesPerFile)) {
            $basePathPattern = $this->getNumberedPathname($basePathname);
        }

        $headers    = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

        $fileCount         = 1;
        $writtenLinesCount = 0;
        foreach ($buffer->getBuffer() as $count => $incompleteItem) {
            if (0 === $writtenLinesCount % $maxLinesPerFile) {
                $filePath = $this->resolveFilePath(
                    $buffer,
                    $maxLinesPerFile,
                    $basePathPattern,
                    $fileCount,
                    $filePathResolverOptions
                );

                $writtenLinesCount = 0;
                $writer            = $this->getWriter($filePath);
                $writer->addRow($headers);
            }

            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);
            $writtenLinesCount++;

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }

            if (0 === $writtenLinesCount % $maxLinesPerFile || $buffer->count() === $count + 1) {
                $writer->close();
                $writtenFiles[] = $filePath;
                $fileCount++;
            }
        }

        return $writtenFiles;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param ColumnSorterInterface $columnSorter
     */
    public function setColumnSorter(ColumnSorterInterface $columnSorter)
    {
        $this->columnSorter = $columnSorter;
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    protected function sortHeaders(array $headers)
    {
        if (null !== $this->columnSorter) {
            $headers = $this->columnSorter->sort($headers);
        }

        return $headers;
    }

    /**
     * @param FlatItemBuffer $buffer
     * @param int            $maxLinesPerFile
     *
     * @return bool
     */
    protected function areSeveralFilesNeeded(FlatItemBuffer $buffer, $maxLinesPerFile)
    {
        if (-1 === $maxLinesPerFile) {
            return false;
        }

        return $buffer->count() > $maxLinesPerFile;
    }

    /**
     * Return the given file path in terms of the current file count if needed
     *
     * @param FlatItemBuffer $buffer
     * @param int            $linesPerFile
     * @param string         $pathPattern
     * @param int            $currentFileCount
     * @param array          $filePathResolverOptions
     *
     * @return string
     */
    protected function resolveFilePath(
        FlatItemBuffer $buffer,
        $linesPerFile,
        $pathPattern,
        $currentFileCount,
        $filePathResolverOptions
    ) {
        $resolvedFilePath = $pathPattern;
        if ($this->areSeveralFilesNeeded($buffer, $linesPerFile)) {
            $resolvedFilePath = $this->filePathResolver->resolve(
                $pathPattern,
                array_merge_recursive(
                    $filePathResolverOptions,
                    ['parameters' => ['%fileNb%' => '_' . $currentFileCount]]
                )
            );
        }

        return $resolvedFilePath;
    }

    /**
     * Return the given path name with %fileNb% placeholder. For instance:
     *     - in -> '/path/myFile.txt' ; out -> '/path/myFile%fileNb%.txt'
     *     - in -> '/path/myFile' ; out -> '/path/myFile%fileNb%'
     *
     * @param string $originalPathname
     *
     * @return string
     */
    protected function getNumberedPathname($originalPathname)
    {
        $fileInfo = new \SplFileInfo($originalPathname);

        $extensionSuffix = '';
        if ('' !== $fileInfo->getExtension()) {
            $extensionSuffix = '.' . $fileInfo->getExtension();
        }

        return $fileInfo->getPath() . DIRECTORY_SEPARATOR .
            $fileInfo->getBasename($extensionSuffix) .
            '%fileNb%' . $extensionSuffix
        ;
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
