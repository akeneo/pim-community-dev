<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Job\RuntimeErrorException;
use Pim\Component\Connector\ArchiveDirectory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Write data into a csv file on the filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter extends AbstractFileWriter implements ArchivableWriterInterface
{
    /** @var FlatItemBuffer */
    protected $buffer;

    /** @var ColumnSorterInterface */
    protected $columnSorter;

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $writtenFiles = [];

    /** @var string */
    protected $pathname;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param ArchiveDirectory          $archiveDirectory
     * @param FlatItemBuffer            $flatRowBuffer
     * @param ColumnSorterInterface     $columnSorter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        ArchiveDirectory $archiveDirectory,
        FlatItemBuffer $flatRowBuffer,
        ColumnSorterInterface $columnSorter
    ) {
        parent::__construct($filePathResolver, $archiveDirectory);

        $this->buffer = $flatRowBuffer;
        $this->columnSorter = $columnSorter;
    }

    /**
     * TODO: should be dropped at the end
     *
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $isWithHeader = $parameters->get('withHeader');
        $this->buffer->write($items, $isWithHeader);
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
    {
        $csvFile = $this->createCsvFile();

        $headers = $this->columnSorter->sort($this->buffer->getHeaders());
        $hollowItem = array_fill_keys($headers, '');
        $this->writeToCsvFile($csvFile, $headers);
        foreach ($this->buffer->getBuffer() as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $this->writeToCsvFile($csvFile, $item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        fclose($csvFile);
        $this->writtenFiles[$this->getPathname()] = basename($this->getPathname());
    }

    /**
     * Create the file to write to and return its pointer
     *
     * @throws RuntimeErrorException
     *
     * @return resource
     */
    protected function createCsvFile()
    {
        if (false === $file = fopen($this->getPathname(), 'w')) {
            throw new RuntimeErrorException('Failed to open file %path%', ['%path%' => $this->getPathname()]);
        }

        return $file;
    }

    /**
     * Write a csv formatted line into the specified file. If an error occurs the file is closed and an exception is
     * thrown.
     *
     * @param resource $csvFile
     * @param array    $data
     *
     * @throws RuntimeErrorException
     */
    protected function writeToCsvFile($csvFile, array $data)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $delimiter = $parameters->get('delimiter');
        $enclosure = $parameters->get('enclosure');
        if (false === fputcsv($csvFile, $data, $delimiter, $enclosure)) {
            fclose($csvFile);
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPathname()]);
        }
    }

    /**
     * @return string
     */
    private function getPathname()
    {
        if (null === $this->pathname) {
            $directory = $this->archiveDirectory->getAbsolute($this->stepExecution->getJobExecution());
            $this->pathname = $directory . $this->getFilename();
        }

       return $this->pathname;
    }
}
