<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Validator\Constraints\File as AssertFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface
{
    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var string */
    protected $filePath;

    /** @var string */
    protected $delimiter = ';';

    /** @var string */
    protected $enclosure = '"';

    /** @var string */
    protected $escape = '\\';

    /** @var bool */
    protected $uploadAllowed = false;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param FileIteratorInterface $fileIterator
     */
    public function __construct(FileIteratorInterface $fileIterator)
    {
        $this->fileIterator = $fileIterator;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->fileIterator->isInitialized()) {
            $this->fileIterator
                ->setReaderOptions([
                    'fieldDelimiter' => $this->delimiter,
                    'fieldEnclosure' => $this->enclosure
                ])
                ->setFilePath($this->filePath)
                ->rewind();
        }

        $this->fileIterator->next();

        $data = $this->fileIterator->current();

        if (null !== $data && null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('read_lines');
        }

        return $data;
    }

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        return [
            new Assert\NotBlank(),
            new AssertFile(
                [
                    'allowedExtensions' => ['csv', 'zip']
                ]
            )
        ];
    }

    /**
     * Set uploaded file
     *
     * @param File $uploadedFile
     *
     * @return CsvReader
     */
    public function setUploadedFile(File $uploadedFile)
    {
        $this->filePath = $uploadedFile->getRealPath();
        $this->fileIterator->reset();

        return $this;
    }

    /**
     * Set file path
     *
     * @param string $filePath
     *
     * @return CsvReader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        $this->fileIterator->reset();

        return $this;
    }

    /**
     * Get file path
     *
     * @return string $filePath
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set delimiter
     *
     * @param string $delimiter
     *
     * @return CsvReader
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get delimiter
     *
     * @return string $delimiter
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set enclosure
     *
     * @param string $enclosure
     *
     * @return CsvReader
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Get enclosure
     *
     * @return string $enclosure
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set escape
     *
     * @param string $escape
     *
     * @return CsvReader
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Get escape
     *
     * @return string $escape
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Set the uploadAllowed property
     *
     * @param bool $uploadAllowed
     *
     * @return CsvReader
     */
    public function setUploadAllowed($uploadAllowed)
    {
        $this->uploadAllowed = $uploadAllowed;

        return $this;
    }

    /**
     * Get the uploadAllowed property
     *
     * @return bool $uploadAllowed
     */
    public function isUploadAllowed()
    {
        return $this->uploadAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.import.filePath.label',
                    'help'  => 'pim_connector.import.filePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.uploadAllowed.label',
                    'help'  => 'pim_connector.import.uploadAllowed.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_connector.import.delimiter.label',
                    'help'  => 'pim_connector.import.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_connector.import.enclosure.label',
                    'help'  => 'pim_connector.import.enclosure.help'
                ]
            ],
            'escape' => [
                'options' => [
                    'label' => 'pim_connector.import.escape.label',
                    'help'  => 'pim_connector.import.escape.help'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
