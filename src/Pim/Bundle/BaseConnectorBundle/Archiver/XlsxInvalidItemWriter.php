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
use Pim\Component\Connector\Writer\File\XlsxSimpleWriter;

/**
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * TODO: Find a better, faster, stronger naming
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

    /** @var XlsxSimpleWriter */
    protected $writer;

    /** @var int */
    protected $batchSize = 100;

    /**
     * CsvInvalidItemWriter constructor.
     *
     * @param InvalidItemsCollector $collector
     * @param XlsxSimpleWriter      $writer
     * @param FileIteratorFactory   $fileIteratorFactory
     * @param Filesystem            $filesystem
     */
    public function __construct(
        InvalidItemsCollector $collector,
        XlsxSimpleWriter $writer,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem $filesystem
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
    }

    protected function read(JobParameters $jobParameters)
    {
        if (null === $this->fileIterator) {
            $filePath = $jobParameters->get('filePath');
            $this->fileIterator = $this->fileIteratorFactory->create($filePath);
            $this->fileIterator->rewind();
        }

        $this->fileIterator->next();

        return $this->fileIterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        if (!$this->collector->getInvalidItems()) {
            return;
        }

        // Parameters for reading the imported XLSX file
        $readJobParameters = $jobExecution->getJobParameters();

        $key = strtr($this->getRelativeArchivePath($jobExecution), ['%filename%' => 'invalid_items.xlsx']);
        $this->filesystem->put($key, '');

        // Parameters for writing the invalid data XLSX file
        $provider = new ProductXlsxExport(new SimpleXlsxExport([]), []);
        $writeParams = $provider->getDefaultValues();
        $writeParams['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $key;

        $writeJobParameters = new JobParameters($writeParams);
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($writeJobParameters);
        $stepExecution = new StepExecution('processor', $jobExecution);

        $this->writer->setStepExecution($stepExecution);

        $invalidLineNumbers = new ArrayCollection();
        foreach ($this->collector->getInvalidItems() as $invalidItem) {
            $invalidLineNumbers->add($invalidItem->getLineNumber());
        }

        $currentLineNumber = $readJobParameters->get('withHeader') ? 1 : 0;
        $writeCount = 0;
        $itemsToWrite = [];

        while (!$invalidLineNumbers->isEmpty()) {
            $readItem = $this->read($readJobParameters);
            $currentLineNumber++;

            if ($invalidLineNumbers->contains($currentLineNumber)) {
                $itemsToWrite[] = $readItem;
                $writeCount++;
                $invalidLineNumbers->removeElement($currentLineNumber);
            }

            if (0 === $writeCount % $this->batchSize && $writeCount > 0) {
                $this->writer->write($itemsToWrite);
                $itemsToWrite = [];
            }
        }

        if (count($itemsToWrite) > 0) {
            $this->writer->write($itemsToWrite);
        }

        $this->writer->flush();
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution)
    {
        return false;
    }

    /**
     * Get the archiver name
     *
     * @return string
     */
    public function getName()
    {
        return 'invalid_xlsx';
    }
}
