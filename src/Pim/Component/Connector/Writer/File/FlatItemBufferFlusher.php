<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Box\Spout\Common\Exception\UnsupportedTypeException;
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
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        ColumnSorterInterface $columnSorter = null
    ) {
        $this->filePathResolver = $filePathResolver;
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
     * @param array          $filePathResolverOptions
     *
     * @return array the list of file paths that have been written
     */
    public function flush(
        FlatItemBuffer $buffer,
        array $writerOptions = [],
        $basePathname,
        $maxLinesPerFile = -1,
        array $filePathResolverOptions = []
    ) {
        if ($this->areSeveralFilesNeeded($buffer, $maxLinesPerFile)) {
            $writtenFiles = $this->writeIntoSeveralFiles(
                $buffer,
                $writerOptions,
                $filePathResolverOptions,
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

        $headers    = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

        $writer = $this->getWriter($filePath, $writerOptions);
        $writer->addRow($headers);

        foreach ($buffer->getBuffer() as $incompleteItem) {
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
     * @param array          $filePathResolverOptions
     * @param int            $maxLinesPerFile
     * @param string         $basePathname
     *
     * @return array
     */
    protected function writeIntoSeveralFiles(
        FlatItemBuffer $buffer,
        array $writerOptions,
        array $filePathResolverOptions = [],
        $maxLinesPerFile,
        $basePathname
    ) {
        $writtenFiles = [];
        $basePathPattern = $this->getNumberedPathname($basePathname);
        $writtenLinesCount = 0;
        $fileCount = 1;

        $headers    = $this->sortHeaders($buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');

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
