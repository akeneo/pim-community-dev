<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;

/**
 * Flushes the flat item buffer into one or multiple output files.
 * @see Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer
 *
 * Several output files are created if the buffer contains more items that maximum lines authorized per output file.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatItemBufferFlusher implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /**
     * @param ColumnSorterInterface $columnSorter
     */
    public function __construct(ColumnSorterInterface $columnSorter = null)
    {
        $this->columnSorter = $columnSorter;
    }

    /**
     * Flushes the flat item buffer into one or multiple output files.
     * Several output files are created if the buffer contains more items that maximum lines authorized per output file.
     *
     * @param FlatItemBuffer $buffer
     * @param array          $writerOptions
     * @param string         $basePathname
     * @param int            $maxLinesPerFile by default -1, which means there is no limit of lines
     *
     * @return array the list of file paths that have been written
     */
    public function flush(
        FlatItemBuffer $buffer,
        array $writerOptions = [],
        $basePathname,
        $maxLinesPerFile = -1
    ) {
        if ($this->areSeveralFilesNeeded($buffer, $maxLinesPerFile)) {
            $writtenFiles = $this->writeIntoSeveralFiles(
                $buffer,
                $writerOptions,
                $maxLinesPerFile,
                $basePathname
            );
        } else {
            $writtenFiles = $this->writeIntoSingleFile($buffer, $writerOptions, $basePathname);
        }

        return $writtenFiles;
    }

    /**
     * @param FlatItemBuffer $buffer
     * @param array          $writerOptions
     * @param string         $filePath
     *
     * @return array
     */
    protected function writeIntoSingleFile(FlatItemBuffer $buffer, array $writerOptions, $filePath)
    {
        $writtenFiles = [];

        $headers = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

        $writer = $this->getWriter($filePath, $writerOptions);
        $writer->addRow($headers);

        foreach ($buffer as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        $writer->close();
        $writtenFiles[] = $filePath;

        return $writtenFiles;
    }

    /**
     * @param FlatItemBuffer $buffer
     * @param array          $writerOptions
     * @param int            $maxLinesPerFile
     * @param string         $basePathname
     *
     * @return array
     */
    protected function writeIntoSeveralFiles(
        FlatItemBuffer $buffer,
        array $writerOptions,
        $maxLinesPerFile,
        $basePathname
    ) {
        $writtenFiles = [];
        $basePathPattern = $this->getNumberedPathname($basePathname);
        $writtenLinesCount = 0;
        $fileCount = 1;

        $headers = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

        foreach ($buffer as $count => $incompleteItem) {
            if (0 === $writtenLinesCount % $maxLinesPerFile) {
                $filePath = $this->resolveFilePath(
                    $buffer,
                    $maxLinesPerFile,
                    $basePathPattern,
                    $fileCount
                );
                $writtenLinesCount = 0;
                $writer = $this->getWriter($filePath, $writerOptions);
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
     * @param array $headers
     *
     * @return array
     */
    protected function sortHeaders(array $headers)
    {
        if (null !== $this->columnSorter) {
            $headers = $this->columnSorter->sort($headers, $this->stepExecution->getJobParameters()->all());
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
     *
     * @return string
     */
    protected function resolveFilePath(
        FlatItemBuffer $buffer,
        $linesPerFile,
        $pathPattern,
        $currentFileCount
    ) {
        $resolvedFilePath = $pathPattern;
        if ($this->areSeveralFilesNeeded($buffer, $linesPerFile)) {
            return strtr($pathPattern, ['%fileNb%' => '_' . $currentFileCount]);
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
     * @param array  $options  Options for Spout writer (delimiter, enclosure, ...)
     *
     * @throws UnsupportedTypeException
     *
     * @return WriterInterface
     */
    protected function getWriter($filePath, array $options = [])
    {
        if (!isset($options['type'])) {
            throw new \InvalidArgumentException('Option "type" have to be defined');
        }

        $writer = WriterFactory::create($options['type']);
        unset($options['type']);

        foreach ($options as $name => $option) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($writer, $setter)) {
                $writer->$setter($option);
            } else {
                $message = sprintf('Option "%s" does not exist in writer "%s"', $setter, get_class($writer));
                throw new \InvalidArgumentException($message);
            }
        }

        $writer->openToFile($filePath);

        return $writer;
    }
}
