<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\FlushableInterface;
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
    StepExecutionAwareInterface,
    FlushableInterface
{
    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param FileIteratorFactory $fileIteratorFactory
     */
    public function __construct(FileIteratorFactory $fileIteratorFactory)
    {
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->fileIterator) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filePath = $jobParameters->getParameter('filePath');
            $delimiter = $jobParameters->getParameter('delimiter');
            $enclosure = $jobParameters->getParameter('enclosure');
            $this->fileIterator = $this->fileIteratorFactory->create($filePath, [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure
            ]);
            $this->fileIterator->rewind();
        }

        $this->fileIterator->next();

        if ($this->fileIterator->valid() && null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('read_lines');
        }

        return $this->fileIterator->current();
    }

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        // TODO : to double check and fix

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
        // TODO : to double check and fix

        $this->filePath     = $uploadedFile->getRealPath();
        $this->fileIterator = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->fileIterator = null;
    }
}
