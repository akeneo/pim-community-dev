<?php

namespace Oro\Bundle\ImportExportBundle\Reader;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;

class CsvFileReader implements ItemReaderInterface, StepExecutionAwareInterface
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
     * @var \SplFileObject
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

    public function __construct(ContextRegistry $contextRegistry)
    {
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if ($this->getFile()->eof()) {
            return null;
        }

        $data = $this->getFile()->fgetcsv();
        if (false !== $data) {
            $context = $this->getContext($this->stepExecution);
            $context->incrementReadOffset();
            if (null === $data || array(null) === $data) {
                return false;
            }
            $context->incrementReadCount();

            if ($this->firstLineIsHeader) {
                if (count($this->header) !== count($data)) {
                    $this->stepExecution->addReaderWarning(
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
            }
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
        $this->stepExecution = $stepExecution;
        $context = $this->getContext($stepExecution);

        if (!$context->hasOption('filePath')) {
            throw new InvalidConfigurationException(
                'Configuration of CSV reader must contain "filePath".'
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

        if ($context->hasOption('escape')) {
            $this->escape = $context->getOption('escape');
        }

        if ($context->hasOption('firstLineIsHeader')) {
            $this->firstLineIsHeader = (bool)$context->getOption('firstLineIsHeader');
        }

        if ($context->hasOption('header')) {
            $this->header = $context->getOption('header');
        }
    }

    /**
     * @param StepExecution $stepExecution
     * @return ContextInterface
     */
    protected function getContext(StepExecution $stepExecution)
    {
        return $this->contextRegistry->getByStepExecution($stepExecution);
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
            throw new InvalidArgumentException(sprintf('File "%s" is not readable.', $this->fileInfo->getRealPath()));
        }
    }
}
