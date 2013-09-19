<?php

namespace Oro\Bundle\ImportExportBundle\Writer;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;

class CsvFileWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /**
     * @var \SplFileInfo
     */
    protected $fileInfo;

    /**
     * @var
     */
    protected $file;

    /**
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var bool
     */
    protected $firstLineIsHeader = true;

    /**
     * @var array
     */
    protected $header;

    /**
     * @var bool
     */
    protected $firstLineWritten = false;

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (!$this->header && count($items) > 0) {
            $this->header = array_keys($items[0]);
        }
        $this->writeHeader();

        foreach ($items as $item) {
            $this->writeCsv($item);
        }
        $this->close();
    }

    /**
     * Write CSV file header.
     */
    protected function writeHeader()
    {
        if (!$this->firstLineWritten && $this->header && $this->firstLineIsHeader) {
            $this->writeCsv($this->header);
        }
    }

    /**
     * Write CSV line.
     *
     * @param array $fields
     */
    protected function writeCsv(array $fields)
    {
        $this->firstLineWritten = true;
        fputcsv($this->getFile(), $fields, $this->delimiter, $this->enclosure);
    }

    /**
     * Get file resource.
     *
     * @return resource
     */
    protected function getFile()
    {
        if (!$this->file) {
            $this->file = fopen($this->fileInfo->getRealPath(), 'a');
        }

        return $this->file;
    }

    /**
     * Close opened file.
     */
    protected function close()
    {
        if ($this->file) {
            fclose($this->file);
            $this->file = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $configuration = $stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();

        if (!isset($configuration['filePath'])) {
            throw new InvalidConfigurationException(
                'Configuration of CSV writer must contain "filePath".'
            );
        } else {
            $this->setFilePath($configuration['filePath']);
        }

        if (isset($configuration['delimiter'])) {
            $this->delimiter = $configuration['delimiter'];
        }

        if (isset($configuration['enclosure'])) {
            $this->enclosure = $configuration['enclosure'];
        }

        if (isset($configuration['firstLineIsHeader'])) {
            $this->firstLineIsHeader = (bool)$configuration['firstLineIsHeader'];
        }

        if (isset($configuration['header'])) {
            $this->header = $configuration['header'];
        }
    }

    /**
     * @param string $filePath
     * @throws InvalidArgumentException
     */
    public function setFilePath($filePath)
    {
        $this->fileInfo = new \SplFileInfo($filePath);
        $dirInfo = new \SplFileInfo($this->fileInfo->getPath());

        if (!$dirInfo->isDir()) {
            throw new InvalidArgumentException(sprintf('Directory "%s" does not exists.', $this->fileInfo->getPath()));
        } elseif (!$dirInfo->isWritable()) {
            throw new InvalidArgumentException(sprintf('Directory "%s" is not writable.', $dirInfo->getRealPath()));
        }
    }
}
