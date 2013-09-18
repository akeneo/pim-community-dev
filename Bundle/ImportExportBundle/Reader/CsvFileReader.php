<?php

namespace Oro\Bundle\ImportExportBundle\Reader;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;

class CsvFileReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /**
     * @var \SplFileInfo
     */
    protected $fileInfo;

    /**
     * @var \SplFileObject
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
     * @var string
     */
    protected $escape = '\\';

    /**
     * @var bool
     */
    protected $firstLineIsHeader = true;

    /**
     * @var array
     */
    protected $header;

    /**
     * @param StepExecution $stepExecution
     * @throws RuntimeException
     * @return object|null|bool
     */
    public function read(StepExecution $stepExecution)
    {
        $data = $this->getFile()->fgetcsv();
        if (false !== $data) {
            if (null === $data || array(null) === $data) {
                return null;
            }
            $stepExecution->incrementReadCount();

            if (count($this->header) !== count($data)) {
                $stepExecution->addReaderWarning(
                    $this,
                    sprintf(
                        'Expecting to get %d columns, actually got %d',
                        count($this->header),
                        count($data)
                    ),
                    $data
                );

                return false;
            }

            $data = array_combine($this->header, $data);
        } else {
            throw new RuntimeException('An error occurred while reading the csv.');
        }

        return $data;
    }

    /**
     * @return \SplFileObject
     */
    protected function getFile()
    {
        if (!$this->file instanceof \SplFileObject) {
            $this->file = $this->fileInfo->openFile();
            $this->file->setFlags(
                \SplFileObject::READ_CSV |
                \SplFileObject::READ_AHEAD |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::DROP_NEW_LINE
            );
            $this->file->setCsvControl(
                $this->delimiter,
                $this->enclosure,
                $this->escape
            );
            if ($this->firstLineIsHeader && !$this->header) {
                $this->header = $this->file->fgetcsv();
            }
        }

        return $this->file;
    }

    /**
     * @param StepExecution $stepExecution
     * @throws InvalidConfigurationException
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $configuration = $stepExecution->getJobExecution()->getJobInstance()->getRawConfiguration();

        if (!isset($configuration['filePath'])) {
            throw new InvalidConfigurationException(
                'Configuration of CSV reader must contain "filePath".'
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

        if (isset($configuration['escape'])) {
            $this->escape = $configuration['escape'];
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

        if (!$this->fileInfo->isFile()) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exists.', $filePath));
        } elseif (!$this->fileInfo->isReadable()) {
            throw new InvalidArgumentException(sprintf('File "%s" is not readable.', $this->fileInfo->getPath()));
        }
    }
}
