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
 * Xlsx Reader
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface
{
    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var string */
    protected $filePath;

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
                    'allowedExtensions' => ['xlsx', 'zip']
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
