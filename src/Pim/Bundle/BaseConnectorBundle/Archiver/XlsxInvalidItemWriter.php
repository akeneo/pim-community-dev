<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductXlsxExport;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleXlsxExport;
use Pim\Component\Connector\Reader\File\FileIterator;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Writer\File\Xlsx\Writer;

/**
 * Writer for invalid items coming from a XLSX import.
 * It writes invalid items (ie. invalid products, families, etc...) into a new XLSX file, available for download.
 *
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class XlsxInvalidItemWriter extends AbstractFilesystemArchiver
{
    /** @var JobExecution */
    protected $jobExecution;

    /** @var FileIterator */
    protected $fileIterator;

    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var InvalidItemsCollector */
    protected $collector;

    /** @var Writer */
    protected $writer;

    /** @var int */
    protected $batchSize = 100;

    /**
     * @param InvalidItemsCollector $collector
     * @param Writer      $writer
     * @param FileIteratorFactory   $fileIteratorFactory
     * @param Filesystem            $filesystem
     */
    public function __construct(
        InvalidItemsCollector $collector,
        Writer $writer,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem $filesystem
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     *
     * Re-parse the imported file and write into a new one the invalid lines.
     */
    public function archive(JobExecution $jobExecution)
    {
        if (!$this->collector->getInvalidItems()) {
            return;
        }

        $invalidLineNumbers = new ArrayCollection();
        foreach ($this->collector->getInvalidItems() as $invalidItem) {
            $invalidLineNumbers->add($invalidItem->getLineNumber());
        }

        $readJobParameters = $jobExecution->getJobParameters();
        $currentLineNumber = 0;
        $itemsToWrite = [];

        $this->setupWriter($jobExecution);

        foreach ($this->getInputFileIterator($readJobParameters) as $readItem) {
            $currentLineNumber++;

            if ($invalidLineNumbers->contains($currentLineNumber)) {
                $itemsToWrite[] = $readItem;
                $invalidLineNumbers->removeElement($currentLineNumber);
            }

            if (count($itemsToWrite) > 0 && 0 === count($itemsToWrite) % $this->batchSize) {
                $this->writer->write($itemsToWrite);
                $itemsToWrite = [];
            }

            if ($invalidLineNumbers->isEmpty()) {
                break;
            }
        }

        if (count($itemsToWrite) > 0) {
            $this->writer->write($itemsToWrite);
        }

        $this->writer->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        if ($jobExecution->getJobParameters()->has('invalid_items_file_format')) {
            return 'xlsx' === $jobExecution->getJobParameters()->get('invalid_items_file_format');
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'invalid_xlsx';
    }

    /**
     * Get the input file iterator to iterate on all the lines of the file.
     *
     * @param JobParameters $jobParameters
     *
     * @return \Iterator
     */
    protected function getInputFileIterator(JobParameters $jobParameters)
    {
        if (null === $this->fileIterator) {
            $filePath = $jobParameters->get('filePath');
            $this->fileIterator = $this->fileIteratorFactory->create($filePath);
            $this->fileIterator->rewind();
        }

        return $this->fileIterator;
    }

    /**
     * Setup the writer with a new JobExecution to write the invalid_items file.
     * We need to setup the writer manually because it's usually set up by the ItemStep.
     *
     * @param JobExecution $jobExecution
     */
    protected function setupWriter(JobExecution $jobExecution)
    {
        $fileKey = strtr($this->getRelativeArchivePath($jobExecution), ['%filename%' => 'invalid_items.xlsx']);
        $this->filesystem->put($fileKey, '');

        $provider = new ProductXlsxExport(new SimpleXlsxExport([]), []);
        $writeParams = $provider->getDefaultValues();
        $writeParams['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $fileKey;

        $writeJobParameters = new JobParameters($writeParams);
        $writeJobExecution  = new JobExecution();
        $writeJobExecution->setJobParameters($writeJobParameters);

        $stepExecution = new StepExecution('processor', $writeJobExecution);
        $this->writer->setStepExecution($stepExecution);
    }
}
