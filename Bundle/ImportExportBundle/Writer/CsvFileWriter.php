<?php

namespace Oro\Bundle\ImportExportBundle\Writer;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;

class CsvFileWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

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
    protected $delimiter = ',';

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
     * @var DoctrineClearWriter
     */
    protected $clearWriter;

    public function __construct(ContextRegistry $contextRegistry)
    {
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * @param DoctrineClearWriter $clearWriter
     */
    public function setClearWriter(DoctrineClearWriter $clearWriter)
    {
        $this->clearWriter = $clearWriter;
    }

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
        if ($this->clearWriter) {
            $this->clearWriter->write($items);
        }
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
     * @throws \Oro\Bundle\ImportExportBundle\Exception\RuntimeException
     */
    protected function writeCsv(array $fields)
    {
        $this->firstLineWritten = true;
        $result = fputcsv($this->getFile(), $fields, $this->delimiter, $this->enclosure);
        if ($result === false) {
            throw new RuntimeException('An error occurred while writing to the csv.');
        }
    }

    /**
     * Get file resource.
     *
     * @return resource
     */
    protected function getFile()
    {
        if (!$this->file) {
            $this->file = fopen($this->fileInfo->getPathname(), 'a');
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
        $context = $this->contextRegistry->getByStepExecution($stepExecution);

        if (!$context->hasOption('filePath')) {
            throw new InvalidConfigurationException(
                'Configuration of CSV writer must contain "filePath".'
            );
        } else {
            $this->setFilePath($context->getOption('filePath'));
        }

        if ($context->hasOption('delimiter')) {
            $this->delimiter = $context->getOption('delimiter');
        }

        if ($context->hasOption('enclosure')) {
            $this->enclosure = $context->getOption('enclosure');
        }

        if ($context->hasOption('firstLineIsHeader')) {
            $this->firstLineIsHeader = (bool)$context->getOption('firstLineIsHeader');
        }

        if ($context->hasOption('header')) {
            $this->header = $context->getOption('header');
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
